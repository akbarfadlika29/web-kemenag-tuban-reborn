<?php

namespace Tests\Feature\Admin\Gallery;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryModuleTest extends TestCase
{
    use RefreshDatabase;

    private function createUnit(): Unit
    {
        return Unit::create([
            'name' => 'Sekretariat',
            'slug' => 'sekretariat',
            'type' => 'unit',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function createMedia(
        ?Unit $unit = null,
        array $overrides = []
    ): Media {
        static $counter = 1;

        $number = $counter++;

        return Media::create(
            array_merge(
                [
                    'unit_id' =>
                        $unit?->id,

                    'disk' =>
                        'public',

                    'path' =>
                        "gallery-test/foto-{$number}.jpg",

                    'original_name' =>
                        "foto-{$number}.jpg",

                    'file_name' =>
                        "foto-{$number}.jpg",

                    'mime_type' =>
                        'image/jpeg',

                    'extension' =>
                        'jpg',

                    'size' =>
                        1024,

                    'type' =>
                        'image',

                    'title' =>
                        "Foto {$number}",

                    'alt_text' =>
                        "Foto {$number}",

                    'is_public' =>
                        true,
                ],
                $overrides
            )
        );
    }

    private function payload(
        Unit $unit,
        array $media,
        array $overrides = []
    ): array {
        $items = [];

        foreach (
            $media as $index => $item
        ) {
            $items[] = [
                'media_id' =>
                    $item->id,

                'caption' =>
                    'Caption '
                    . ($index + 1),

                'alt_text' =>
                    'Alt '
                    . ($index + 1),

                'sort_order' =>
                    $index,
            ];
        }

        return array_merge(
            [
                'unit_id' =>
                    $unit->id,

                'cover_media_id' =>
                    $media[0]->id
                    ?? null,

                'title' =>
                    'Dokumentasi Kegiatan',

                'excerpt' =>
                    null,

                'description' =>
                    'Dokumentasi kegiatan resmi instansi.',

                'status' =>
                    'draft',

                'published_at' =>
                    null,

                'is_featured' =>
                    '0',

                'meta_title' =>
                    null,

                'meta_description' =>
                    null,

                'items' =>
                    $items,
            ],
            $overrides
        );
    }

    public function test_gallery_can_be_created(): void
    {
        $unit =
            $this->createUnit();

        $media1 =
            $this->createMedia(
                $unit
            );

        $media2 =
            $this->createMedia(
                $unit
            );

        $response =
            $this->post(
                route(
                    'admin.galleries.store'
                ),
                $this->payload(
                    $unit,
                    [
                        $media1,
                        $media2,
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.galleries.index'
            )
        );

        $gallery =
            Gallery::firstOrFail();

        $this->assertSame(
            'dokumentasi-kegiatan',
            $gallery->slug
        );

        $this->assertSame(
            2,
            $gallery
                ->items()
                ->count()
        );
    }

    public function test_gallery_requires_unit(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $response =
            $this->post(
                route(
                    'admin.galleries.store'
                ),
                $this->payload(
                    $unit,
                    [$media],
                    [
                        'unit_id' =>
                            null,
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'unit_id'
        );
    }

    public function test_gallery_requires_at_least_one_photo(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.galleries.store'
                ),
                $this->payload(
                    $unit,
                    [],
                    [
                        'items' =>
                            [],
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'items'
        );
    }

    public function test_duplicate_media_is_rejected(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $payload =
            $this->payload(
                $unit,
                [$media]
            );

        $payload['items'] = [
            [
                'media_id' =>
                    $media->id,

                'caption' =>
                    'Pertama',

                'alt_text' =>
                    'Pertama',

                'sort_order' =>
                    0,
            ],

            [
                'media_id' =>
                    $media->id,

                'caption' =>
                    'Kedua',

                'alt_text' =>
                    'Kedua',

                'sort_order' =>
                    1,
            ],
        ];

        $response =
            $this->post(
                route(
                    'admin.galleries.store'
                ),
                $payload
            );

        $response->assertSessionHasErrors(
            'items.1.media_id'
        );
    }

    public function test_slug_is_generated_automatically(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media],
                [
                    'title' =>
                        'Upacara Hari Amal Bakti',
                ]
            )
        );

        $this->assertDatabaseHas(
            'galleries',
            [
                'slug' =>
                    'upacara-hari-amal-bakti',
            ]
        );
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        $unit =
            $this->createUnit();

        $media1 =
            $this->createMedia(
                $unit
            );

        $media2 =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media1],
                [
                    'title' =>
                        'Kegiatan Kantor',
                ]
            )
        );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media2],
                [
                    'title' =>
                        'Kegiatan Kantor',
                ]
            )
        );

        $this->assertDatabaseHas(
            'galleries',
            [
                'slug' =>
                    'kegiatan-kantor',
            ]
        );

        $this->assertDatabaseHas(
            'galleries',
            [
                'slug' =>
                    'kegiatan-kantor-2',
            ]
        );
    }

    public function test_excerpt_is_generated_from_description(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media],
                [
                    'excerpt' =>
                        null,

                    'description' =>
                        'Dokumentasi pembinaan pegawai Kementerian Agama.',
                ]
            )
        );

        $gallery =
            Gallery::firstOrFail();

        $this->assertStringContainsString(
            'Dokumentasi pembinaan pegawai',
            $gallery->excerpt
        );
    }

    public function test_meta_title_defaults_to_gallery_title(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media],
                [
                    'title' =>
                        'Galeri Pelayanan',

                    'meta_title' =>
                        null,
                ]
            )
        );

        $this->assertDatabaseHas(
            'galleries',
            [
                'title' =>
                    'Galeri Pelayanan',

                'meta_title' =>
                    'Galeri Pelayanan',
            ]
        );
    }

    public function test_published_gallery_without_date_gets_publication_date(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media],
                [
                    'status' =>
                        'published',

                    'published_at' =>
                        null,
                ]
            )
        );

        $gallery =
            Gallery::firstOrFail();

        $this->assertNotNull(
            $gallery->published_at
        );

        $this->assertTrue(
            $gallery->isPublished()
        );
    }

    public function test_future_publication_is_scheduled(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $this->payload(
                $unit,
                [$media],
                [
                    'status' =>
                        'published',

                    'published_at' =>
                        now()
                            ->addDay()
                            ->format(
                                'Y-m-d H:i:s'
                            ),
                ]
            )
        );

        $gallery =
            Gallery::firstOrFail();

        $this->assertSame(
            'scheduled',
            $gallery->publication_state
        );

        $this->assertFalse(
            Gallery::published()
                ->whereKey(
                    $gallery->id
                )
                ->exists()
        );
    }

    public function test_gallery_item_order_is_saved(): void
    {
        $unit =
            $this->createUnit();

        $media1 =
            $this->createMedia(
                $unit
            );

        $media2 =
            $this->createMedia(
                $unit
            );

        $payload =
            $this->payload(
                $unit,
                [
                    $media1,
                    $media2,
                ]
            );

        $payload['items'] = [
            [
                'media_id' =>
                    $media2->id,

                'caption' =>
                    'Foto kedua',

                'alt_text' =>
                    'Foto kedua',

                'sort_order' =>
                    0,
            ],

            [
                'media_id' =>
                    $media1->id,

                'caption' =>
                    'Foto pertama',

                'alt_text' =>
                    'Foto pertama',

                'sort_order' =>
                    1,
            ],
        ];

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $payload
        );

        $gallery =
            Gallery::firstOrFail();

        $items =
            $gallery
                ->items()
                ->get();

        $this->assertSame(
            $media2->id,
            $items[0]->media_id
        );

        $this->assertSame(
            $media1->id,
            $items[1]->media_id
        );
    }

    public function test_caption_and_alt_text_are_saved(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $payload =
            $this->payload(
                $unit,
                [$media]
            );

        $payload['items'][0]['caption'] =
            'Pembukaan acara';

        $payload['items'][0]['alt_text'] =
            'Foto pembukaan acara';

        $this->post(
            route(
                'admin.galleries.store'
            ),
            $payload
        );

        $gallery =
            Gallery::firstOrFail();

        $item =
            $gallery
                ->items()
                ->firstOrFail();

        $this->assertSame(
            'Pembukaan acara',
            $item->caption
        );

        $this->assertSame(
            'Foto pembukaan acara',
            $item->alt_text
        );
    }

    public function test_gallery_can_be_updated_and_items_resynced(): void
    {
        $unit =
            $this->createUnit();

        $media1 =
            $this->createMedia(
                $unit
            );

        $media2 =
            $this->createMedia(
                $unit
            );

        $gallery =
            Gallery::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Galeri Lama',

                'slug' =>
                    'galeri-lama',

                'description' =>
                    'Deskripsi lama',

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $gallery
            ->items()
            ->create([
                'media_id' =>
                    $media1->id,

                'caption' =>
                    'Lama',

                'sort_order' =>
                    0,
            ]);

        $response =
            $this->put(
                route(
                    'admin.galleries.update',
                    $gallery
                ),
                $this->payload(
                    $unit,
                    [$media2],
                    [
                        'title' =>
                            'Galeri Baru',
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.galleries.index'
            )
        );

        $gallery->refresh();

        $this->assertSame(
            'Galeri Baru',
            $gallery->title
        );

        $this->assertSame(
            'galeri-baru',
            $gallery->slug
        );

        $this->assertDatabaseMissing(
            'gallery_items',
            [
                'gallery_id' =>
                    $gallery->id,

                'media_id' =>
                    $media1->id,
            ]
        );

        $this->assertDatabaseHas(
            'gallery_items',
            [
                'gallery_id' =>
                    $gallery->id,

                'media_id' =>
                    $media2->id,
            ]
        );
    }

    public function test_gallery_can_be_soft_deleted(): void
    {
        $unit =
            $this->createUnit();

        $media =
            $this->createMedia(
                $unit
            );

        $gallery =
            Gallery::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Galeri Hapus',

                'slug' =>
                    'galeri-hapus',

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $gallery
            ->items()
            ->create([
                'media_id' =>
                    $media->id,

                'sort_order' =>
                    0,
            ]);

        $response =
            $this->delete(
                route(
                    'admin.galleries.destroy',
                    $gallery
                )
            );

        $response->assertRedirect(
            route(
                'admin.galleries.index'
            )
        );

        $this->assertSoftDeleted(
            'galleries',
            [
                'id' =>
                    $gallery->id,
            ]
        );

        $this->assertDatabaseMissing(
            'gallery_items',
            [
                'gallery_id' =>
                    $gallery->id,
            ]
        );

        $this->assertDatabaseHas(
            'media',
            [
                'id' =>
                    $media->id,
            ]
        );
    }

    public function test_index_can_search_by_title(): void
    {
        $unit =
            $this->createUnit();

        Gallery::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Dokumentasi Haji',

            'slug' =>
                'dokumentasi-haji',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        Gallery::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Dokumentasi ASN',

            'slug' =>
                'dokumentasi-asn',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.galleries.index',
                    [
                        'search' =>
                            'Haji',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'galleries',
            function ($galleries) {
                return
                    $galleries->total() === 1
                    && $galleries
                        ->first()
                        ->title ===
                        'Dokumentasi Haji';
            }
        );
    }

    public function test_index_can_filter_featured(): void
    {
        $unit =
            $this->createUnit();

        Gallery::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Unggulan',

            'slug' =>
                'unggulan',

            'status' =>
                'draft',

            'is_featured' =>
                true,
        ]);

        Gallery::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Normal',

            'slug' =>
                'normal',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.galleries.index',
                    [
                        'is_featured' =>
                            '1',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'galleries',
            function ($galleries) {
                return
                    $galleries->total() === 1
                    && $galleries
                        ->first()
                        ->title ===
                        'Unggulan';
            }
        );
    }
}