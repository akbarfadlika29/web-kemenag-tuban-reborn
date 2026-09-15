<?php

namespace Tests\Feature\Admin\Ppid;

use App\Models\Media;
use App\Models\PpidCategory;
use App\Models\PpidInformation;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpidModuleTest extends TestCase
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

    private function createCategory(
        array $overrides = []
    ): PpidCategory {
        return PpidCategory::create(
            array_merge([
                'name' => 'Profil Badan Publik',
                'slug' => 'profil-badan-publik',
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
                'path' => "ppid-test/dokumen-{$number}.pdf",
                'original_name' => "dokumen-{$number}.pdf",
                'file_name' => "dokumen-{$number}.pdf",
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size' => 2048,
                'type' => 'document',
                'title' => "Dokumen {$number}",
                'alt_text' => null,
                'is_public' => true,
            ], $overrides)
        );
    }

    private function payload(
        Unit $unit,
        PpidCategory $category,
        array $media,
        array $overrides = []
    ): array {
        $documents = [];

        foreach ($media as $index => $item) {
            $documents[] = [
                'media_id' => $item->id,
                'title' => $item->title,
                'description' => null,
                'version' => '1.0',
                'document_status' => 'active',
                'document_date' => now()->format('Y-m-d'),
                'sort_order' => $index,
                'is_primary' => $index === 0 ? '1' : '0',
            ];
        }

        return array_merge([
            'category_id' => $category->id,
            'unit_id' => $unit->id,

            'title' =>
                'Informasi Profil Instansi',

            'classification' =>
                'berkala',

            'excerpt' =>
                null,

            'description' =>
                'Informasi mengenai profil instansi dan pelayanan publik.',

            'information_holder' =>
                'Sekretariat',

            'person_in_charge' =>
                'Kepala Subbagian Tata Usaha',

            'information_form' =>
                'digital',

            'information_format' =>
                'PDF',

            'publication_media' =>
                'Website PPID',

            'retention_period' =>
                5,

            'retention_unit' =>
                'year',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'document_number' =>
                '001/PPID/2026',

            'document_date' =>
                now()->format('Y-m-d'),

            'effective_date' =>
                now()->format('Y-m-d'),

            'last_reviewed_at' =>
                now()->format('Y-m-d H:i:s'),

            'legal_basis' =>
                'Peraturan mengenai keterbukaan informasi publik.',

            'notes' =>
                null,

            'year' =>
                2026,

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

            'documents' =>
                $documents,
        ], $overrides);
    }

    public function test_ppid_category_can_be_created(): void
    {
        $response =
            $this->post(
                route(
                    'admin.ppid-categories.store'
                ),
                [
                    'name' =>
                        'Informasi Keuangan',

                    'description' =>
                        'Informasi keuangan badan publik.',

                    'sort_order' =>
                        1,

                    'is_active' =>
                        '1',
                ]
            );

        $response->assertRedirect(
            route(
                'admin.ppid-categories.index'
            )
        );

        $this->assertDatabaseHas(
            'ppid_categories',
            [
                'name' =>
                    'Informasi Keuangan',

                'slug' =>
                    'informasi-keuangan',
            ]
        );
    }

    public function test_category_slug_gets_suffix(): void
    {
        $this->createCategory();

        $this->post(
            route(
                'admin.ppid-categories.store'
            ),
            [
                'name' =>
                    'Profil Badan Publik',

                'description' =>
                    null,

                'sort_order' =>
                    2,

                'is_active' =>
                    '1',
            ]
        );

        $this->assertDatabaseHas(
            'ppid_categories',
            [
                'slug' =>
                    'profil-badan-publik-2',
            ]
        );
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        PpidInformation::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Informasi',

            'slug' =>
                'informasi',

            'classification' =>
                'berkala',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->delete(
                route(
                    'admin.ppid-categories.destroy',
                    $category
                )
            );

        $response->assertSessionHas(
            'error'
        );

        $this->assertDatabaseHas(
            'ppid_categories',
            [
                'id' =>
                    $category->id,

                'deleted_at' =>
                    null,
            ]
        );
    }

    public function test_ppid_information_can_be_created(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

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
                    'admin.ppid-informations.store'
                ),
                $this->payload(
                    $unit,
                    $category,
                    [
                        $media1,
                        $media2,
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.ppid-informations.index'
            )
        );

        $information =
            PpidInformation::firstOrFail();

        $this->assertSame(
            'informasi-profil-instansi',
            $information->slug
        );

        $this->assertSame(
            2,
            $information
                ->documents()
                ->count()
        );
    }

    public function test_information_requires_category(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media =
            $this->createMedia(
                $unit
            );

        $response =
            $this->post(
                route(
                    'admin.ppid-informations.store'
                ),
                $this->payload(
                    $unit,
                    $category,
                    [$media],
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

    public function test_information_requires_unit(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media =
            $this->createMedia(
                $unit
            );

        $response =
            $this->post(
                route(
                    'admin.ppid-informations.store'
                ),
                $this->payload(
                    $unit,
                    $category,
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

    public function test_information_requires_document(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $response =
            $this->post(
                route(
                    'admin.ppid-informations.store'
                ),
                $this->payload(
                    $unit,
                    $category,
                    [],
                    [
                        'documents' =>
                            [],
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'documents'
        );
    }

    public function test_duplicate_document_is_rejected(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media =
            $this->createMedia(
                $unit
            );

        $payload =
            $this->payload(
                $unit,
                $category,
                [$media]
            );

        $payload['documents'][] =
            $payload['documents'][0];

        $response =
            $this->post(
                route(
                    'admin.ppid-informations.store'
                ),
                $payload
            );

        $response->assertSessionHasErrors(
            'documents.1.media_id'
        );
    }

    public function test_slug_is_generated_automatically(): void
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
                'admin.ppid-informations.store'
            ),
            $this->payload(
                $unit,
                $category,
                [$media],
                [
                    'title' =>
                        'Laporan Kinerja Tahunan',
                ]
            )
        );

        $this->assertDatabaseHas(
            'ppid_informations',
            [
                'slug' =>
                    'laporan-kinerja-tahunan',
            ]
        );
    }

    public function test_published_without_date_gets_publication_date(): void
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
                'admin.ppid-informations.store'
            ),
            $this->payload(
                $unit,
                $category,
                [$media],
                [
                    'status' =>
                        'published',

                    'published_at' =>
                        null,
                ]
            )
        );

        $information =
            PpidInformation::firstOrFail();

        $this->assertNotNull(
            $information->published_at
        );

        $this->assertTrue(
            $information->isPublished()
        );
    }

    public function test_future_publication_is_scheduled(): void
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
                'admin.ppid-informations.store'
            ),
            $this->payload(
                $unit,
                $category,
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

        $information =
            PpidInformation::firstOrFail();

        $this->assertSame(
            'scheduled',
            $information->publication_state
        );

        $this->assertFalse(
            PpidInformation::published()
                ->whereKey(
                    $information->id
                )
                ->exists()
        );
    }

    public function test_permanent_retention_clears_period(): void
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
                'admin.ppid-informations.store'
            ),
            $this->payload(
                $unit,
                $category,
                [$media],
                [
                    'retention_period' =>
                        10,

                    'retention_unit' =>
                        'permanent',
                ]
            )
        );

        $information =
            PpidInformation::firstOrFail();

        $this->assertNull(
            $information->retention_period
        );

        $this->assertSame(
            'permanent',
            $information->retention_unit
        );
    }

    public function test_only_one_primary_document_is_saved(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media1 =
            $this->createMedia($unit);

        $media2 =
            $this->createMedia($unit);

        $payload =
            $this->payload(
                $unit,
                $category,
                [
                    $media1,
                    $media2,
                ]
            );

        $payload['documents'][0]['is_primary'] =
            '1';

        $payload['documents'][1]['is_primary'] =
            '1';

        $this->post(
            route(
                'admin.ppid-informations.store'
            ),
            $payload
        );

        $information =
            PpidInformation::firstOrFail();

        $this->assertSame(
            1,
            $information
                ->documents()
                ->where(
                    'is_primary',
                    true
                )
                ->count()
        );
    }

    public function test_first_document_becomes_primary_when_none_selected(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media1 =
            $this->createMedia($unit);

        $media2 =
            $this->createMedia($unit);

        $payload =
            $this->payload(
                $unit,
                $category,
                [
                    $media1,
                    $media2,
                ]
            );

        $payload['documents'][0]['is_primary'] =
            '0';

        $payload['documents'][1]['is_primary'] =
            '0';

        $this->post(
            route(
                'admin.ppid-informations.store'
            ),
            $payload
        );

        $information =
            PpidInformation::firstOrFail();

        $primary =
            $information
                ->documents()
                ->where(
                    'is_primary',
                    true
                )
                ->firstOrFail();

        $this->assertSame(
            $media1->id,
            $primary->media_id
        );
    }

    public function test_information_can_be_updated_and_documents_resynced(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media1 =
            $this->createMedia($unit);

        $media2 =
            $this->createMedia($unit);

        $information =
            PpidInformation::create([
                'category_id' =>
                    $category->id,

                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Informasi Lama',

                'slug' =>
                    'informasi-lama',

                'classification' =>
                    'berkala',

                'availability' =>
                    'online',

                'access_level' =>
                    'public',

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $information
            ->documents()
            ->create([
                'media_id' =>
                    $media1->id,

                'title' =>
                    'Dokumen Lama',

                'document_status' =>
                    'active',

                'sort_order' =>
                    0,

                'is_primary' =>
                    true,
            ]);

        $response =
            $this->put(
                route(
                    'admin.ppid-informations.update',
                    $information
                ),
                $this->payload(
                    $unit,
                    $category,
                    [$media2],
                    [
                        'title' =>
                            'Informasi Baru',
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.ppid-informations.index'
            )
        );

        $information->refresh();

        $this->assertSame(
            'Informasi Baru',
            $information->title
        );

        $this->assertSame(
            'informasi-baru',
            $information->slug
        );

        $this->assertDatabaseMissing(
            'ppid_information_documents',
            [
                'information_id' =>
                    $information->id,

                'media_id' =>
                    $media1->id,
            ]
        );

        $this->assertDatabaseHas(
            'ppid_information_documents',
            [
                'information_id' =>
                    $information->id,

                'media_id' =>
                    $media2->id,
            ]
        );
    }

    public function test_information_can_be_deleted_without_deleting_media(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        $media =
            $this->createMedia(
                $unit
            );

        $information =
            PpidInformation::create([
                'category_id' =>
                    $category->id,

                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Hapus Informasi',

                'slug' =>
                    'hapus-informasi',

                'classification' =>
                    'berkala',

                'availability' =>
                    'online',

                'access_level' =>
                    'public',

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $information
            ->documents()
            ->create([
                'media_id' =>
                    $media->id,

                'document_status' =>
                    'active',

                'sort_order' =>
                    0,

                'is_primary' =>
                    true,
            ]);

        $response =
            $this->delete(
                route(
                    'admin.ppid-informations.destroy',
                    $information
                )
            );

        $response->assertRedirect(
            route(
                'admin.ppid-informations.index'
            )
        );

        $this->assertSoftDeleted(
            'ppid_informations',
            [
                'id' =>
                    $information->id,
            ]
        );

        $this->assertDatabaseMissing(
            'ppid_information_documents',
            [
                'information_id' =>
                    $information->id,
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

        $category =
            $this->createCategory();

        PpidInformation::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Laporan Haji',

            'slug' =>
                'laporan-haji',

            'classification' =>
                'berkala',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        PpidInformation::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Laporan Kepegawaian',

            'slug' =>
                'laporan-kepegawaian',

            'classification' =>
                'berkala',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.ppid-informations.index',
                    [
                        'search' =>
                            'Haji',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'informations',
            function ($informations) {
                return
                    $informations->total() === 1
                    && $informations
                        ->first()
                        ->title ===
                        'Laporan Haji';
            }
        );
    }

    public function test_index_can_filter_classification(): void
    {
        $unit =
            $this->createUnit();

        $category =
            $this->createCategory();

        PpidInformation::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Berkala',

            'slug' =>
                'berkala',

            'classification' =>
                'berkala',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        PpidInformation::create([
            'category_id' =>
                $category->id,

            'unit_id' =>
                $unit->id,

            'title' =>
                'Serta Merta',

            'slug' =>
                'serta-merta',

            'classification' =>
                'serta_merta',

            'availability' =>
                'online',

            'access_level' =>
                'public',

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.ppid-informations.index',
                    [
                        'classification' =>
                            'serta_merta',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'informations',
            function ($informations) {
                return
                    $informations->total() === 1
                    && $informations
                        ->first()
                        ->title ===
                        'Serta Merta';
            }
        );
    }
}