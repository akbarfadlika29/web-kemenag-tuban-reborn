<?php

namespace Tests\Feature\Admin\Page;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageModuleTest extends TestCase
{
    use RefreshDatabase;

    private function payload(
        array $overrides = []
    ): array {
        return array_merge(
            [
                'parent_id' => null,
                'unit_id' => null,
                'cover_media_id' => null,

                'title' => 'Profil Instansi',

                'excerpt' => null,

                'content' =>
                    '<p>Informasi resmi mengenai profil instansi.</p>',

                'template' => 'default',

                'status' => 'draft',

                'published_at' => null,

                'show_in_menu' => '1',

                'sort_order' => 1,

                'meta_title' => null,

                'meta_description' => null,
            ],
            $overrides
        );
    }

    private function createPage(
        array $overrides = []
    ): Page {
        return Page::create(
            array_merge(
                [
                    'parent_id' => null,
                    'unit_id' => null,
                    'cover_media_id' => null,

                    'title' => 'Profil',

                    'slug' => 'profil',

                    'excerpt' => null,

                    'content' => '<p>Profil</p>',

                    'template' => 'default',

                    'status' => 'draft',

                    'published_at' => null,

                    'show_in_menu' => true,

                    'sort_order' => 1,

                    'meta_title' => null,

                    'meta_description' => null,
                ],
                $overrides
            )
        );
    }

    public function test_page_can_be_created(): void
    {
        $response =
            $this->post(
                route(
                    'admin.pages.store'
                ),
                $this->payload()
            );

        $response->assertRedirect(
            route(
                'admin.pages.index'
            )
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'title' => 'Profil Instansi',
                'slug' => 'profil-instansi',
                'status' => 'draft',
                'show_in_menu' => true,
            ]
        );
    }

    public function test_slug_is_generated_automatically(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'title' => 'Visi dan Misi',
            ])
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'title' => 'Visi dan Misi',
                'slug' => 'visi-dan-misi',
            ]
        );
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'title' => 'Tentang Kami',
            ])
        );

        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'title' => 'Tentang Kami',
            ])
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'slug' => 'tentang-kami',
            ]
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'slug' => 'tentang-kami-2',
            ]
        );
    }

    public function test_html_is_sanitized(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'content' =>
                    '<p>Aman</p><script>alert("x")</script>',
            ])
        );

        $page =
            Page::firstOrFail();

        $this->assertStringContainsString(
            '<p>Aman</p>',
            $page->content
        );

        $this->assertStringNotContainsString(
            '<script',
            $page->content
        );
    }

    public function test_content_without_real_text_is_rejected(): void
    {
        $response =
            $this->post(
                route(
                    'admin.pages.store'
                ),
                $this->payload([
                    'content' =>
                        '<script>alert("x")</script>',
                ])
            );

        $response->assertSessionHasErrors(
            'content'
        );

        $this->assertDatabaseCount(
            'pages',
            0
        );
    }

    public function test_excerpt_is_generated_when_empty(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'excerpt' => null,

                'content' =>
                    '<p>Ini adalah isi halaman untuk membuat ringkasan otomatis.</p>',
            ])
        );

        $page =
            Page::firstOrFail();

        $this->assertStringContainsString(
            'Ini adalah isi halaman',
            $page->excerpt
        );
    }

    public function test_meta_title_defaults_to_page_title(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'title' => 'Profil Pelayanan',
                'meta_title' => null,
            ])
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'title' => 'Profil Pelayanan',
                'meta_title' => 'Profil Pelayanan',
            ]
        );
    }

    public function test_meta_description_defaults_to_excerpt(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'excerpt' =>
                    'Ringkasan halaman untuk kebutuhan SEO.',
                'meta_description' => null,
            ])
        );

        $page =
            Page::firstOrFail();

        $this->assertSame(
            'Ringkasan halaman untuk kebutuhan SEO.',
            $page->meta_description
        );
    }

    public function test_published_page_without_date_gets_publication_date(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'status' => 'published',
                'published_at' => null,
            ])
        );

        $page =
            Page::firstOrFail();

        $this->assertNotNull(
            $page->published_at
        );

        $this->assertTrue(
            $page->isPublished()
        );

        $this->assertSame(
            'published',
            $page->publication_state
        );
    }

    public function test_future_publication_is_scheduled(): void
    {
        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'status' => 'published',

                'published_at' =>
                    now()
                        ->addDay()
                        ->format(
                            'Y-m-d H:i:s'
                        ),
            ])
        );

        $page =
            Page::firstOrFail();

        $this->assertSame(
            'scheduled',
            $page->publication_state
        );

        $this->assertFalse(
            $page->isPublished()
        );

        $this->assertFalse(
            Page::published()
                ->whereKey(
                    $page->id
                )
                ->exists()
        );
    }

    public function test_page_can_have_parent(): void
    {
        $parent =
            $this->createPage([
                'title' => 'Profil',
                'slug' => 'profil',
            ]);

        $this->post(
            route(
                'admin.pages.store'
            ),
            $this->payload([
                'title' => 'Visi dan Misi',
                'parent_id' => $parent->id,
            ])
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'title' => 'Visi dan Misi',
                'parent_id' => $parent->id,
            ]
        );
    }

    public function test_page_can_be_updated(): void
    {
        $page =
            $this->createPage();

        $response =
            $this->put(
                route(
                    'admin.pages.update',
                    $page
                ),
                $this->payload([
                    'title' =>
                        'Profil Instansi Baru',

                    'content' =>
                        '<p>Konten baru halaman.</p>',
                ])
            );

        $response->assertRedirect(
            route(
                'admin.pages.index'
            )
        );

        $page->refresh();

        $this->assertSame(
            'Profil Instansi Baru',
            $page->title
        );

        $this->assertSame(
            'profil-instansi-baru',
            $page->slug
        );

        $this->assertStringContainsString(
            'Konten baru halaman.',
            $page->content
        );
    }

    public function test_slug_is_regenerated_when_title_is_updated(): void
    {
        $page =
            $this->createPage([
                'title' =>
                    'Tentang Instansi',

                'slug' =>
                    'tentang-instansi',
            ]);

        $this->put(
            route(
                'admin.pages.update',
                $page
            ),
            $this->payload([
                'title' =>
                    'Tentang Kementerian',
            ])
        );

        $page->refresh();

        $this->assertSame(
            'tentang-kementerian',
            $page->slug
        );
    }

    public function test_page_cannot_be_its_own_parent(): void
    {
        $page =
            $this->createPage();

        $response =
            $this->put(
                route(
                    'admin.pages.update',
                    $page
                ),
                $this->payload([
                    'title' =>
                        $page->title,

                    'parent_id' =>
                        $page->id,
                ])
            );

        $response->assertSessionHasErrors(
            'parent_id'
        );
    }

    public function test_descendant_cannot_become_parent(): void
    {
        $parent =
            $this->createPage([
                'title' => 'Profil',
                'slug' => 'profil',
            ]);

        $child =
            $this->createPage([
                'parent_id' =>
                    $parent->id,

                'title' =>
                    'Visi',

                'slug' =>
                    'visi',
            ]);

        $response =
            $this->put(
                route(
                    'admin.pages.update',
                    $parent
                ),
                $this->payload([
                    'title' =>
                        'Profil',

                    'parent_id' =>
                        $child->id,
                ])
            );

        $response->assertSessionHasErrors(
            'parent_id'
        );
    }

    public function test_page_without_children_can_be_deleted(): void
    {
        $page =
            $this->createPage();

        $response =
            $this->delete(
                route(
                    'admin.pages.destroy',
                    $page
                )
            );

        $response->assertRedirect(
            route(
                'admin.pages.index'
            )
        );

        $this->assertSoftDeleted(
            'pages',
            [
                'id' => $page->id,
            ]
        );
    }

    public function test_page_with_children_cannot_be_deleted(): void
    {
        $parent =
            $this->createPage([
                'title' => 'Profil',
                'slug' => 'profil',
            ]);

        $this->createPage([
            'parent_id' =>
                $parent->id,

            'title' =>
                'Sejarah',

            'slug' =>
                'sejarah',
        ]);

        $response =
            $this->delete(
                route(
                    'admin.pages.destroy',
                    $parent
                )
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertDatabaseHas(
            'pages',
            [
                'id' => $parent->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_page_index_can_be_opened(): void
    {
        $this->createPage();

        $response =
            $this->get(
                route(
                    'admin.pages.index'
                )
            );

        $response->assertOk();

        $response->assertSee(
            'Profil'
        );
    }

public function test_page_index_can_search_by_title(): void
{
    $profil =
        $this->createPage([
            'title' => 'Profil Instansi',
            'slug' => 'profil-instansi',
        ]);

    $haji =
        $this->createPage([
            'title' => 'Layanan Haji',
            'slug' => 'layanan-haji',
        ]);

    $response =
        $this->get(
            route(
                'admin.pages.index',
                [
                    'search' => 'Haji',
                ]
            )
        );

    $response->assertOk();

    $response->assertViewHas(
        'pages',
        function ($pages) use ($profil, $haji) {
            $ids =
                $pages
                    ->getCollection()
                    ->pluck('id')
                    ->all();

            return
                in_array(
                    $haji->id,
                    $ids,
                    true
                )
                && !in_array(
                    $profil->id,
                    $ids,
                    true
                );
        }
    );
}

public function test_page_index_can_filter_by_status(): void
{
    $draft =
        $this->createPage([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'status' => 'draft',
        ]);

    $published =
        $this->createPage([
            'title' => 'Published Page',
            'slug' => 'published-page',
            'status' => 'published',
            'published_at' => now(),
        ]);

    $response =
        $this->get(
            route(
                'admin.pages.index',
                [
                    'status' => 'published',
                ]
            )
        );

    $response->assertOk();

    $response->assertViewHas(
        'pages',
        function ($pages) use ($draft, $published) {
            $ids =
                $pages
                    ->getCollection()
                    ->pluck('id')
                    ->all();

            return
                in_array(
                    $published->id,
                    $ids,
                    true
                )
                && !in_array(
                    $draft->id,
                    $ids,
                    true
                );
        }
    );
}

public function test_page_index_can_filter_menu_visibility(): void
{
    $menuPage =
        $this->createPage([
            'title' => 'Menu Page',
            'slug' => 'menu-page',
            'show_in_menu' => true,
        ]);

    $hiddenPage =
        $this->createPage([
            'title' => 'Hidden Page',
            'slug' => 'hidden-page',
            'show_in_menu' => false,
        ]);

    $response =
        $this->get(
            route(
                'admin.pages.index',
                [
                    'show_in_menu' => '1',
                ]
            )
        );

    $response->assertOk();

    $response->assertViewHas(
        'pages',
        function ($pages) use (
            $menuPage,
            $hiddenPage
        ) {
            $ids =
                $pages
                    ->getCollection()
                    ->pluck('id')
                    ->all();

            return
                in_array(
                    $menuPage->id,
                    $ids,
                    true
                )
                && !in_array(
                    $hiddenPage->id,
                    $ids,
                    true
                );
        }
    );
}
}