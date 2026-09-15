<?php

namespace Tests\Feature\Admin\Service;

use App\Models\Media;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceModuleTest extends TestCase
{
    use RefreshDatabase;

    private function createUnit(): Unit
    {
        return Unit::create([
            'name' => 'Sekretariat',
            'slug' => 'sekretariat',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function createCategory(
        array $overrides = []
    ): ServiceCategory {
        return ServiceCategory::create(
            array_merge([
                'name' => 'Pelayanan Administrasi',
                'slug' => 'pelayanan-administrasi',
                'description' => null,
                'sort_order' => 1,
                'is_active' => true,
            ], $overrides)
        );
    }

    private function createMedia(
        ?Unit $unit = null,
        array $overrides = []
    ): Media {
        static $counter = 1;

        $number = $counter++;

        return Media::create(
            array_merge([
                'unit_id' => $unit?->id,
                'disk' => 'public',
                'path' => "service-test/cover-{$number}.jpg",
                'original_name' => "cover-{$number}.jpg",
                'file_name' => "cover-{$number}.jpg",
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'size' => 1024,
                'type' => 'image',
                'title' => "Cover {$number}",
                'alt_text' => "Cover layanan {$number}",
                'is_public' => true,
            ], $overrides)
        );
    }

    private function payload(
        Unit $unit,
        ServiceCategory $category,
        array $overrides = []
    ): array {
        return array_merge([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'cover_media_id' =>
                null,

            'title' =>
                'Pelayanan Administrasi',

            'excerpt' =>
                null,

            'description' =>
                'Pelayanan administrasi untuk masyarakat.',

            'requirements' =>
                'Membawa identitas diri.',

            'procedure' =>
                'Datang ke kantor dan menyerahkan berkas.',

            'completion_time' =>
                '1 hari kerja',

            'is_free' =>
                '1',

            'fee_description' =>
                null,

            'service_output' =>
                'Dokumen pelayanan.',

            'legal_basis' =>
                'Peraturan yang berlaku.',

            'service_channel' =>
                'offline',

            'service_url' =>
                null,

            'service_location' =>
                'Kantor Kementerian Agama',

            'service_hours' =>
                'Senin-Jumat 08.00-16.00',

            'contact_name' =>
                'Petugas Pelayanan',

            'contact_phone' =>
                '0356-000000',

            'contact_email' =>
                'pelayanan@example.test',

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
        ], $overrides);
    }

    public function test_service_category_can_be_created(): void
    {
        $response =
            $this->post(
                route(
                    'admin.service-categories.store'
                ),
                [
                    'name' =>
                        'Pelayanan Haji',

                    'description' =>
                        'Pelayanan haji dan umrah.',

                    'sort_order' =>
                        1,

                    'is_active' =>
                        '1',
                ]
            );

        $response->assertRedirect(
            route(
                'admin.service-categories.index'
            )
        );

        $this->assertDatabaseHas(
            'service_categories',
            [
                'name' =>
                    'Pelayanan Haji',

                'slug' =>
                    'pelayanan-haji',
            ]
        );
    }

    public function test_category_duplicate_slug_gets_suffix(): void
    {
        $this->createCategory();

        $this->post(
            route(
                'admin.service-categories.store'
            ),
            [
                'name' =>
                    'Pelayanan Administrasi',

                'description' =>
                    null,

                'sort_order' =>
                    2,

                'is_active' =>
                    '1',
            ]
        );

        $this->assertDatabaseHas(
            'service_categories',
            [
                'slug' =>
                    'pelayanan-administrasi-2',
            ]
        );
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Layanan',

            'slug' =>
                'layanan',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->delete(
                route(
                    'admin.service-categories.destroy',
                    $category
                )
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertDatabaseHas(
            'service_categories',
            [
                'id' =>
                    $category->id,

                'deleted_at' =>
                    null,
            ]
        );
    }

    public function test_service_can_be_created(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $response =
            $this->post(
                route(
                    'admin.services.store'
                ),
                $this->payload(
                    $unit,
                    $category
                )
            );

        $response->assertRedirect(
            route(
                'admin.services.index'
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'title' =>
                    'Pelayanan Administrasi',

                'slug' =>
                    'pelayanan-administrasi',

                'unit_id' =>
                    $unit->id,

                'category_id' =>
                    $category->id,
            ]
        );
    }

    public function test_service_requires_category(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $response =
            $this->post(
                route(
                    'admin.services.store'
                ),
                $this->payload(
                    $unit,
                    $category,
                    [
                        'category_id' =>
                            null,
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'category_id'
        );
    }

    public function test_service_requires_unit(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $response =
            $this->post(
                route(
                    'admin.services.store'
                ),
                $this->payload(
                    $unit,
                    $category,
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

    public function test_slug_is_generated_automatically(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'title' =>
                        'Pendaftaran Haji Reguler',
                ]
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'slug' =>
                    'pendaftaran-haji-reguler',
            ]
        );
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'title' =>
                        'Pelayanan Publik',
                ]
            )
        );

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'title' =>
                        'Pelayanan Publik',
                ]
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'slug' =>
                    'pelayanan-publik',
            ]
        );

        $this->assertDatabaseHas(
            'services',
            [
                'slug' =>
                    'pelayanan-publik-2',
            ]
        );
    }

    public function test_excerpt_is_generated_from_description(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'excerpt' =>
                        null,

                    'description' =>
                        'Pelayanan untuk membantu masyarakat memperoleh dokumen administrasi.',
                ]
            )
        );

        $service =
            Service::firstOrFail();

        $this->assertStringContainsString(
            'Pelayanan untuk membantu masyarakat',
            $service->excerpt
        );
    }

    public function test_free_service_clears_fee_description(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'is_free' =>
                        '1',

                    'fee_description' =>
                        'Rp100.000',
                ]
            )
        );

        $service =
            Service::firstOrFail();

        $this->assertTrue(
            $service->is_free
        );

        $this->assertNull(
            $service->fee_description
        );
    }

    public function test_paid_service_can_store_fee_description(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'is_free' =>
                        '0',

                    'fee_description' =>
                        'Sesuai tarif yang berlaku.',
                ]
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'is_free' =>
                    false,

                'fee_description' =>
                    'Sesuai tarif yang berlaku.',
            ]
        );
    }

    public function test_offline_service_clears_service_url(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'service_channel' =>
                        'offline',

                    'service_url' =>
                        'https://example.test/layanan',
                ]
            )
        );

        $service =
            Service::firstOrFail();

        $this->assertNull(
            $service->service_url
        );
    }

    public function test_online_service_can_store_url(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'service_channel' =>
                        'online',

                    'service_url' =>
                        'https://example.test/layanan',
                ]
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'service_channel' =>
                    'online',

                'service_url' =>
                    'https://example.test/layanan',
            ]
        );
    }

    public function test_cover_can_be_selected_from_media_manager(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media =
            $this->createMedia(
                $unit
            );

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'cover_media_id' =>
                        $media->id,
                ]
            )
        );

        $this->assertDatabaseHas(
            'services',
            [
                'cover_media_id' =>
                    $media->id,
            ]
        );
    }

    public function test_published_service_without_date_gets_publication_date(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
                [
                    'status' =>
                        'published',

                    'published_at' =>
                        null,
                ]
            )
        );

        $service =
            Service::firstOrFail();

        $this->assertNotNull(
            $service->published_at
        );

        $this->assertTrue(
            $service->isPublished()
        );
    }

    public function test_future_publication_is_scheduled(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $this->post(
            route(
                'admin.services.store'
            ),
            $this->payload(
                $unit,
                $category,
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

        $service =
            Service::firstOrFail();

        $this->assertSame(
            'scheduled',
            $service->publication_state
        );

        $this->assertFalse(
            Service::published()
                ->whereKey(
                    $service->id
                )
                ->exists()
        );
    }

    public function test_service_can_be_updated_and_slug_regenerated(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $service =
            Service::create([
                'category_id' =>
                    $category->id,

                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Layanan Lama',

                'slug' =>
                    'layanan-lama',

                'service_channel' =>
                    'offline',

                'is_free' =>
                    true,

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $response =
            $this->put(
                route(
                    'admin.services.update',
                    $service
                ),
                $this->payload(
                    $unit,
                    $category,
                    [
                        'title' =>
                            'Layanan Baru',
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.services.index'
            )
        );

        $service->refresh();

        $this->assertSame(
            'Layanan Baru',
            $service->title
        );

        $this->assertSame(
            'layanan-baru',
            $service->slug
        );
    }

    public function test_service_can_be_soft_deleted(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $service =
            Service::create([
                'category_id' =>
                    $category->id,

                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Layanan Hapus',

                'slug' =>
                    'layanan-hapus',

                'service_channel' =>
                    'offline',

                'is_free' =>
                    true,

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $response =
            $this->delete(
                route(
                    'admin.services.destroy',
                    $service
                )
            );

        $response->assertRedirect(
            route(
                'admin.services.index'
            )
        );

        $this->assertSoftDeleted(
            'services',
            [
                'id' =>
                    $service->id,
            ]
        );
    }

    public function test_index_uses_postgresql_case_insensitive_search(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Pelayanan HAJI',

            'slug' =>
                'pelayanan-haji',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Pelayanan Kepegawaian',

            'slug' =>
                'pelayanan-kepegawaian',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.services.index',
                    [
                        'search' =>
                            'haji',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'services',
            function ($services) {
                return
                    $services->total() === 1
                    && $services
                        ->first()
                        ->title ===
                        'Pelayanan HAJI';
            }
        );
    }

    public function test_index_can_filter_service_channel(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Online',

            'slug' =>
                'online',

            'service_channel' =>
                'online',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Offline',

            'slug' =>
                'offline',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.services.index',
                    [
                        'service_channel' =>
                            'online',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'services',
            function ($services) {
                return
                    $services->total() === 1
                    && $services
                        ->first()
                        ->title ===
                        'Online';
            }
        );
    }

    public function test_index_can_filter_featured_services(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Unggulan',

            'slug' =>
                'unggulan',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                true,
        ]);

        Service::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Normal',

            'slug' =>
                'normal',

            'service_channel' =>
                'offline',

            'is_free' =>
                true,

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.services.index',
                    [
                        'is_featured' =>
                            '1',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'services',
            function ($services) {
                return
                    $services->total() === 1
                    && $services
                        ->first()
                        ->title ===
                        'Unggulan';
            }
        );
    }
}