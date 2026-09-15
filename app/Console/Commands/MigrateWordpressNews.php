<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MigrateWordpressNews extends Command
{
    protected $signature = 'wordpress:migrate-news
        {--dry-run : Hanya membaca dan menghitung tanpa mengubah database/file}
        {--limit=0 : Batasi jumlah berita. 0 = semua berita}
        {--start-page=1 : Mulai dari halaman WordPress tertentu}
        {--pages=0 : Jumlah halaman yang diproses. 0 = sampai habis}';

    protected $description = 'Migrasi berita WordPress Kemenag Tuban ke database PPID';

    private const WP_BASE_URL = 'https://kemenagtuban.com';

    private array $categoryCache = [];
    private array $tagCache = [];
    private array $mediaCache = [];

    private array $dryCategories = [];
    private array $dryTags = [];
    private array $dryMedia = [];

    private int $processed = 0;
    private int $createdNews = 0;
    private int $updatedNews = 0;
    private int $mediaImported = 0;
    private int $mediaMissing = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));
        $startPage = max(1, (int) $this->option('start-page'));
        $maxPages = max(0, (int) $this->option('pages'));

        $this->newLine();
        $this->info('WordPress → PPID News Migrator');
        $this->line('Sumber : '.self::WP_BASE_URL);
        $this->line('Mode   : '.($dryRun ? 'DRY RUN' : 'IMPORT'));
        $this->line('Limit  : '.($limit === 0 ? 'SEMUA' : $limit));
        $this->line('Mulai  : WordPress page '.$startPage);
        $this->line('Pages  : '.($maxPages === 0 ? 'SAMPAI HABIS' : $maxPages));

        if ($dryRun) {
            $this->warn('DRY RUN: database dan storage tidak akan diubah.');
        }

        $this->newLine();

        try {
            $page = $startPage;
            $perPage = 100;
            $pagesProcessed = 0;

            while (true) {
                $response = Http::acceptJson()
                    ->timeout(60)
                    ->retry(3, 1000)
                    ->get(
                        self::WP_BASE_URL.'/wp-json/wp/v2/posts',
                        [
                            'page' => $page,
                            'per_page' => $perPage,
                            'status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'desc',
                            '_fields' => implode(',', [
                                'id',
                                'date',
                                'modified',
                                'slug',
                                'status',
                                'title',
                                'excerpt',
                                'content',
                                'featured_media',
                                'categories',
                                'tags',
                                'sticky',
                            ]),
                        ]
                    );

                if (!$response->successful()) {
                    /*
                     * WordPress mengembalikan 400 saat page sudah
                     * melewati halaman terakhir.
                     */
                    if ($response->status() === 400 && $page > 1) {
                        break;
                    }

                    $this->error(
                        "Gagal membaca WordPress page {$page}. HTTP {$response->status()}"
                    );

                    return self::FAILURE;
                }

                $posts = $response->json();

                if (!is_array($posts) || count($posts) === 0) {
                    break;
                }

                foreach ($posts as $post) {
                    if ($limit > 0 && $this->processed >= $limit) {
                        break 2;
                    }

                    $this->processed++;

                    try {
                        $this->processPost(
                            $post,
                            $dryRun
                        );
                    } catch (Throwable $e) {
                        $this->failed++;

                        $id = $post['id'] ?? '?';

                        $this->error(
                            "Post {$id} gagal: {$e->getMessage()}"
                        );

                        report($e);
                    }
                }

                $pagesProcessed++;

                if ($maxPages > 0 && $pagesProcessed >= $maxPages) {
                    break;
                }

                if (count($posts) < $perPage) {
                    break;
                }

                $page++;
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->showSummary($dryRun);

        return $this->failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function processPost(
        array $post,
        bool $dryRun
    ): void {
        $wordpressId = (int) ($post['id'] ?? 0);

        if ($wordpressId <= 0) {
            throw new \RuntimeException(
                'WordPress post ID tidak valid.'
            );
        }

        $title = $this->plainText(
            data_get($post, 'title.rendered', '')
        );

        $slug = trim((string) ($post['slug'] ?? ''));

        if ($slug === '') {
            $slug = Str::slug($title);
        }

        if ($slug === '') {
            $slug = 'wordpress-'.$wordpressId;
        }

        $excerpt = $this->plainText(
            data_get($post, 'excerpt.rendered', '')
        );

        $content = (string) data_get(
            $post,
            'content.rendered',
            ''
        );

        $categoryIds = array_values(
            array_filter(
                array_map(
                    'intval',
                    $post['categories'] ?? []
                )
            )
        );

        /*
         * Jika ada kategori selain WordPress "Uncategorized" (ID 1),
         * gunakan kategori tersebut terlebih dahulu.
         */
        $primaryCategoryId = null;

        foreach ($categoryIds as $categoryId) {
            if ($categoryId !== 1) {
                $primaryCategoryId = $categoryId;
                break;
            }
        }

        $primaryCategoryId ??= $categoryIds[0] ?? null;

        $tagIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $post['tags'] ?? []
                    )
                )
            )
        );

        $featuredMediaId = (int) (
            $post['featured_media'] ?? 0
        );

        /*
         * Ambil semua attachment yang tertanam di HTML.
         *
         * Contoh:
         * class="wp-image-21665"
         */
        $inlineMediaIds = [];

        if (
            preg_match_all(
                '/wp-image-(\d+)/i',
                $content,
                $matches
            )
        ) {
            $inlineMediaIds = array_values(
                array_unique(
                    array_map(
                        'intval',
                        $matches[1]
                    )
                )
            );
        }

        if ($dryRun) {
            if ($primaryCategoryId) {
                $this->dryCategories[$primaryCategoryId] = true;
            }

            foreach ($tagIds as $tagId) {
                $this->dryTags[$tagId] = true;
            }

            if ($featuredMediaId > 0) {
                $this->dryMedia[$featuredMediaId] = true;
            }

            foreach ($inlineMediaIds as $mediaId) {
                $this->dryMedia[$mediaId] = true;
            }

            $this->line(
                sprintf(
                    '[DRY] #%d %s',
                    $wordpressId,
                    Str::limit($title, 80)
                )
            );

            return;
        }

        /*
         * CATEGORY
         */
        $category = null;

        if ($primaryCategoryId) {
            $category = $this->importCategory(
                $primaryCategoryId
            );
        }

        /*
         * FEATURED/COVER IMAGE
         */
        $coverMedia = null;

        if ($featuredMediaId > 0) {
            $coverMedia = $this->importMedia(
                $featuredMediaId
            );
        }

        /*
         * INLINE IMAGES
         */
        foreach ($inlineMediaIds as $mediaId) {
            $media = $this->importMedia(
                $mediaId
            );

            if (!$media) {
                continue;
            }

            $content = $this->rewriteInlineImage(
                $content,
                $mediaId,
                '/storage/'.ltrim(
                    $media->path,
                    '/'
                )
            );
        }

        /*
         * TAGS
         */
        $tagModels = [];

        foreach ($tagIds as $tagId) {
            $tag = $this->importTag($tagId);

            if ($tag) {
                $tagModels[] = $tag;
            }
        }

        /*
         * NEWS
         */
        $news = News::withTrashed()
            ->where(
                'wordpress_post_id',
                $wordpressId
            )
            ->first();

        $wasExisting = $news !== null;

        if ($news) {
            /*
             * Pertahankan slug yang sudah diberikan pada import
             * sebelumnya agar URL berita legacy tetap stabil ketika
             * command dijalankan ulang.
             */
            $slug = $news->slug;
        } else {
            /*
             * Hindari collision dengan berita lokal yang kebetulan
             * sudah memakai slug yang sama.
             */
            $slugExists = News::withTrashed()
                ->where('slug', $slug)
                ->exists();

            if ($slugExists) {
                $slug .= '-wp-'.$wordpressId;
            }

            $news = new News();
        }

        if (
            method_exists($news, 'trashed')
            && $news->trashed()
        ) {
            $news->restore();
        }

        $news->forceFill([
            'wordpress_post_id' => $wordpressId,

            /*
             * Sengaja nullable.
             * Data legacy tidak dipaksa memiliki unit/user lokal.
             */
            'unit_id' => null,
            'author_id' => null,

            'category_id' => $category?->id,
            'cover_media_id' => $coverMedia?->id,

            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt !== ''
                ? $excerpt
                : null,
            'content' => $content,

            /*
             * WordPress yang kita ambil hanya status=publish.
             */
            'status' => 'published',

            'published_at' => $post['date'] ?? null,

            /*
             * WordPress REST API expose field "sticky".
             */
            'is_featured' => (bool) (
                $post['sticky'] ?? false
            ),

            /*
             * View count lama tidak tersedia secara standar melalui
             * WordPress REST API.
             */
            'view_count' => $news->exists
                ? $news->view_count
                : 0,
        ]);

        $news->save();

        /*
         * Pertahankan tanggal asli WordPress.
         */
        if (!empty($post['date'])) {
            $news->created_at = $post['date'];
        }

        if (!empty($post['modified'])) {
            $news->updated_at = $post['modified'];
        }

        $news->saveQuietly();

        $news->tags()->sync(
            collect($tagModels)
                ->pluck('id')
                ->all()
        );

        if ($wasExisting) {
            $this->updatedNews++;
            $state = 'UPDATE';
        } else {
            $this->createdNews++;
            $state = 'CREATE';
        }

        $this->info(
            sprintf(
                '[%s] #%d %s',
                $state,
                $wordpressId,
                Str::limit($title, 80)
            )
        );
    }

    private function importCategory(
        int $wordpressTermId
    ): ?NewsCategory {
        if (
            array_key_exists(
                $wordpressTermId,
                $this->categoryCache
            )
        ) {
            return $this->categoryCache[
                $wordpressTermId
            ];
        }

        $term = $this->getWordpressTerm(
            'categories',
            $wordpressTermId
        );

        if (!$term) {
            return $this->categoryCache[
                $wordpressTermId
            ] = null;
        }

        $name = $this->plainText(
            $term['name'] ?? ''
        );

        $slug = trim(
            (string) ($term['slug'] ?? '')
        );

        if ($slug === '') {
            $slug = Str::slug($name);
        }

        if ($slug === '') {
            $slug = 'wp-category-'.$wordpressTermId;
        }

        $category = NewsCategory::withTrashed()
            ->where(
                'wordpress_term_id',
                $wordpressTermId
            )
            ->first();

        if (!$category) {
            /*
             * Bila kategori dengan slug yang sama sudah dibuat
             * secara manual, gunakan record tersebut.
             */
            $category = NewsCategory::withTrashed()
                ->where('slug', $slug)
                ->first();
        }

        $category ??= new NewsCategory();

        if (
            method_exists($category, 'trashed')
            && $category->trashed()
        ) {
            $category->restore();
        }

        $category->forceFill([
            'wordpress_term_id' => $wordpressTermId,
            'name' => $name,
            'slug' => $slug,
            'description' => $this->plainText(
                $term['description'] ?? ''
            ) ?: null,
            'is_active' => true,
        ]);

        $category->save();

        return $this->categoryCache[
            $wordpressTermId
        ] = $category;
    }

    private function importTag(
        int $wordpressTermId
    ): ?NewsTag {
        if (
            array_key_exists(
                $wordpressTermId,
                $this->tagCache
            )
        ) {
            return $this->tagCache[
                $wordpressTermId
            ];
        }

        $term = $this->getWordpressTerm(
            'tags',
            $wordpressTermId
        );

        if (!$term) {
            return $this->tagCache[
                $wordpressTermId
            ] = null;
        }

        $name = $this->plainText(
            $term['name'] ?? ''
        );

        $slug = trim(
            (string) ($term['slug'] ?? '')
        );

        if ($slug === '') {
            $slug = Str::slug($name);
        }

        if ($slug === '') {
            $slug = 'wp-tag-'.$wordpressTermId;
        }

        $tag = NewsTag::withTrashed()
            ->where(
                'wordpress_term_id',
                $wordpressTermId
            )
            ->first();

        if (!$tag) {
            $tag = NewsTag::withTrashed()
                ->where('slug', $slug)
                ->first();
        }

        $tag ??= new NewsTag();

        if (
            method_exists($tag, 'trashed')
            && $tag->trashed()
        ) {
            $tag->restore();
        }

        $tag->forceFill([
            'wordpress_term_id' => $wordpressTermId,
            'name' => $name,
            'slug' => $slug,
        ]);

        $tag->save();

        return $this->tagCache[
            $wordpressTermId
        ] = $tag;
    }

    private function getWordpressTerm(
        string $type,
        int $id
    ): ?array {
        try {
            $response = Http::acceptJson()
                ->timeout(30)
                ->retry(3, 500)
                ->get(
                    self::WP_BASE_URL.
                    "/wp-json/wp/v2/{$type}/{$id}",
                    [
                        '_fields' => implode(',', [
                            'id',
                            'name',
                            'slug',
                            'description',
                        ]),
                    ]
                );

            if (!$response->successful()) {
                $this->warn(
                    "Term {$type} #{$id} tidak dapat dibaca."
                );

                return null;
            }

            return $response->json();
        } catch (Throwable $e) {
            $this->warn(
                "Term {$type} #{$id} gagal: {$e->getMessage()}"
            );

            return null;
        }
    }

    private function importMedia(
        int $wordpressAttachmentId
    ): ?Media {
        if (
            array_key_exists(
                $wordpressAttachmentId,
                $this->mediaCache
            )
        ) {
            return $this->mediaCache[
                $wordpressAttachmentId
            ];
        }

        /*
         * Jika pernah berhasil diimport, langsung pakai record lama.
         */
        $existing = Media::withTrashed()
            ->where(
                'wordpress_attachment_id',
                $wordpressAttachmentId
            )
            ->first();

        if (
            $existing
            && Storage::disk(
                $existing->disk
            )->exists(
                $existing->path
            )
        ) {
            if (
                method_exists($existing, 'trashed')
                && $existing->trashed()
            ) {
                $existing->restore();
            }

            return $this->mediaCache[
                $wordpressAttachmentId
            ] = $existing;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(30)
                ->retry(3, 500)
                ->get(
                    self::WP_BASE_URL.
                    "/wp-json/wp/v2/media/{$wordpressAttachmentId}",
                    [
                        '_fields' => implode(',', [
                            'id',
                            'date',
                            'slug',
                            'title',
                            'caption',
                            'alt_text',
                            'mime_type',
                            'source_url',
                        ]),
                    ]
                );

            if (!$response->successful()) {
                $this->mediaMissing++;

                $this->warn(
                    "Media #{$wordpressAttachmentId}: metadata tidak tersedia."
                );

                return $this->mediaCache[
                    $wordpressAttachmentId
                ] = null;
            }

            $remote = $response->json();

            $sourceUrl = trim(
                (string) (
                    $remote['source_url'] ?? ''
                )
            );

            $mimeType = trim(
                (string) (
                    $remote['mime_type'] ?? ''
                )
            );

            if (
                $sourceUrl === ''
                || !str_starts_with(
                    strtolower($mimeType),
                    'image/'
                )
            ) {
                return $this->mediaCache[
                    $wordpressAttachmentId
                ] = null;
            }

            /*
             * Hanya izinkan media dari domain WordPress kita sendiri.
             */
            $host = strtolower(
                (string) parse_url(
                    $sourceUrl,
                    PHP_URL_HOST
                )
            );

            if (
                !in_array(
                    $host,
                    [
                        'kemenagtuban.com',
                        'www.kemenagtuban.com',
                    ],
                    true
                )
            ) {
                $this->warn(
                    "Media #{$wordpressAttachmentId}: domain tidak diizinkan."
                );

                return $this->mediaCache[
                    $wordpressAttachmentId
                ] = null;
            }

            $download = Http::timeout(60)
                ->retry(3, 1000)
                ->get($sourceUrl);

            /*
             * Contoh gambar lama 2020 yang sudah hilang akan
             * masuk ke cabang ini (HTTP 404), tetapi berita tetap jalan.
             */
            if (!$download->successful()) {
                $this->mediaMissing++;

                $this->warn(
                    "Media #{$wordpressAttachmentId}: HTTP {$download->status()} — dilewati."
                );

                return $this->mediaCache[
                    $wordpressAttachmentId
                ] = null;
            }

            $body = $download->body();

            if ($body === '') {
                $this->mediaMissing++;

                return $this->mediaCache[
                    $wordpressAttachmentId
                ] = null;
            }

            $urlPath = urldecode(
                (string) parse_url(
                    $sourceUrl,
                    PHP_URL_PATH
                )
            );

            $originalName = basename($urlPath);

            $extension = strtolower(
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                )
            );

            if ($extension === '') {
                $extension = match ($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif',
                    default => 'bin',
                };
            }

            $baseName = pathinfo(
                $originalName,
                PATHINFO_FILENAME
            );

            $safeBaseName = Str::slug(
                $baseName
            );

            if ($safeBaseName === '') {
                $safeBaseName = 'image';
            }

            /*
             * Pertahankan struktur tahun/bulan WordPress jika tersedia.
             */
            $year = date(
                'Y',
                strtotime(
                    $remote['date'] ?? 'now'
                )
            );

            $month = date(
                'm',
                strtotime(
                    $remote['date'] ?? 'now'
                )
            );

            $fileName =
                $wordpressAttachmentId.
                '-'.
                $safeBaseName.
                '.'.
                $extension;

            $path =
                "wordpress/news/{$year}/{$month}/{$fileName}";

            Storage::disk('public')->put(
                $path,
                $body
            );

            $media = $existing
                ?: new Media();

            if (
                method_exists($media, 'trashed')
                && $media->trashed()
            ) {
                $media->restore();
            }

            $media->forceFill([
                'wordpress_attachment_id'
                    => $wordpressAttachmentId,

                'unit_id' => null,

                'original_name'
                    => $originalName,

                'file_name'
                    => $fileName,

                'path'
                    => $path,

                'disk'
                    => 'public',

                'mime_type'
                    => $mimeType,

                'extension'
                    => $extension,

                'size'
                    => strlen($body),

                'type'
                    => 'image',

                'title'
                    => $this->plainText(
                        data_get(
                            $remote,
                            'title.rendered',
                            ''
                        )
                    ) ?: null,

                'alt_text'
                    => $this->plainText(
                        $remote['alt_text'] ?? ''
                    ) ?: null,

                'description'
                    => $this->plainText(
                        data_get(
                            $remote,
                            'caption.rendered',
                            ''
                        )
                    ) ?: null,

                'is_public'
                    => true,
            ]);

            $media->save();

            $this->mediaImported++;

            return $this->mediaCache[
                $wordpressAttachmentId
            ] = $media;
        } catch (Throwable $e) {
            $this->mediaMissing++;

            $this->warn(
                "Media #{$wordpressAttachmentId} gagal: {$e->getMessage()}"
            );

            report($e);

            return $this->mediaCache[
                $wordpressAttachmentId
            ] = null;
        }
    }

    private function rewriteInlineImage(
        string $html,
        int $attachmentId,
        string $localUrl
    ): string {
        /*
         * Cari IMG yang memiliki class wp-image-{ID}.
         *
         * SRC diganti ke URL lokal.
         * SRCSET/SIZES dibuang agar browser tidak lagi mencoba
         * mengambil thumbnail dari WordPress lama.
         */
        return preg_replace_callback(
            '/<img\b[^>]*>/i',
            function (array $match) use (
                $attachmentId,
                $localUrl
            ) {
                $tag = $match[0];

                if (
                    !preg_match(
                        '/\bwp-image-'.
                        preg_quote(
                            (string) $attachmentId,
                            '/'
                        ).
                        '\b/i',
                        $tag
                    )
                ) {
                    return $tag;
                }

                $escapedUrl = htmlspecialchars(
                    $localUrl,
                    ENT_QUOTES,
                    'UTF-8'
                );

                if (
                    preg_match(
                        '/\bsrc=(["\']).*?\1/i',
                        $tag
                    )
                ) {
                    $tag = preg_replace(
                        '/\bsrc=(["\']).*?\1/i',
                        'src="'.$escapedUrl.'"',
                        $tag,
                        1
                    );
                } else {
                    $tag = preg_replace(
                        '/<img\b/i',
                        '<img src="'.$escapedUrl.'"',
                        $tag,
                        1
                    );
                }

                $tag = preg_replace(
                    '/\s+srcset=(["\']).*?\1/i',
                    '',
                    $tag
                );

                $tag = preg_replace(
                    '/\s+sizes=(["\']).*?\1/i',
                    '',
                    $tag
                );

                return $tag;
            },
            $html
        ) ?? $html;
    }

    private function plainText(
        mixed $value
    ): string {
        return trim(
            html_entity_decode(
                strip_tags(
                    (string) $value
                ),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }

    private function showSummary(
        bool $dryRun
    ): void {
        $this->newLine(2);

        $this->info('==============================');
        $this->info('RINGKASAN MIGRASI');
        $this->info('==============================');

        $this->table(
            ['Item', 'Jumlah'],
            $dryRun
                ? [
                    [
                        'Berita ditemukan',
                        $this->processed,
                    ],
                    [
                        'Kategori WordPress unik',
                        count(
                            $this->dryCategories
                        ),
                    ],
                    [
                        'Tag WordPress unik',
                        count(
                            $this->dryTags
                        ),
                    ],
                    [
                        'Attachment potensial',
                        count(
                            $this->dryMedia
                        ),
                    ],
                    [
                        'Gagal',
                        $this->failed,
                    ],
                ]
                : [
                    [
                        'Berita diproses',
                        $this->processed,
                    ],
                    [
                        'Berita baru',
                        $this->createdNews,
                    ],
                    [
                        'Berita diperbarui',
                        $this->updatedNews,
                    ],
                    [
                        'Media berhasil',
                        $this->mediaImported,
                    ],
                    [
                        'Media hilang/gagal',
                        $this->mediaMissing,
                    ],
                    [
                        'Berita gagal',
                        $this->failed,
                    ],
                ]
        );

        if ($dryRun) {
            $this->warn(
                'Belum ada perubahan pada database maupun storage.'
            );
        }
    }
}
