<?php

namespace Tests\Feature\Admin\Agenda;

use App\Models\Agenda;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaModuleTest extends TestCase
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

    private function payload(
        Unit $unit,
        array $overrides = []
    ): array {
        return array_merge([
            'unit_id' => $unit->id,
            'cover_media_id' => null,

            'title' => 'Rapat Koordinasi',

            'excerpt' => null,

            'content' =>
                '<p>Agenda rapat koordinasi instansi.</p>',

            'location' =>
                'Aula Kantor',

            'start_at' =>
                now()
                    ->addDay()
                    ->format('Y-m-d H:i:s'),

            'end_at' =>
                now()
                    ->addDay()
                    ->addHours(2)
                    ->format('Y-m-d H:i:s'),

            'status' => 'draft',

            'published_at' => null,

            'is_featured' => '0',

            'meta_title' => null,

            'meta_description' => null,
        ], $overrides);
    }

    public function test_agenda_can_be_created(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.agendas.store'
                ),
                $this->payload(
                    $unit
                )
            );

        $response->assertRedirect(
            route(
                'admin.agendas.index'
            )
        );

        $this->assertDatabaseHas(
            'agendas',
            [
                'title' =>
                    'Rapat Koordinasi',

                'slug' =>
                    'rapat-koordinasi',

                'unit_id' =>
                    $unit->id,
            ]
        );
    }

    public function test_unit_is_required(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.agendas.store'
                ),
                $this->payload(
                    $unit,
                    [
                        'unit_id' => null,
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'unit_id'
        );
    }

    public function test_start_date_is_required(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.agendas.store'
                ),
                $this->payload(
                    $unit,
                    [
                        'start_at' => null,
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'start_at'
        );
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.agendas.store'
                ),
                $this->payload(
                    $unit,
                    [
                        'start_at' =>
                            now()
                                ->addDay()
                                ->format(
                                    'Y-m-d H:i:s'
                                ),

                        'end_at' =>
                            now()
                                ->format(
                                    'Y-m-d H:i:s'
                                ),
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'end_at'
        );
    }

    public function test_slug_is_generated_automatically(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'title' =>
                        'Pembinaan ASN',
                ]
            )
        );

        $this->assertDatabaseHas(
            'agendas',
            [
                'slug' =>
                    'pembinaan-asn',
            ]
        );
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'title' =>
                        'Rapat Bulanan',
                ]
            )
        );

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'title' =>
                        'Rapat Bulanan',
                ]
            )
        );

        $this->assertDatabaseHas(
            'agendas',
            [
                'slug' =>
                    'rapat-bulanan',
            ]
        );

        $this->assertDatabaseHas(
            'agendas',
            [
                'slug' =>
                    'rapat-bulanan-2',
            ]
        );
    }

    public function test_html_is_sanitized(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'content' =>
                        '<p>Aman</p><script>alert(1)</script>',
                ]
            )
        );

        $agenda =
            Agenda::firstOrFail();

        $this->assertStringContainsString(
            '<p>Aman</p>',
            $agenda->content
        );

        $this->assertStringNotContainsString(
            '<script',
            $agenda->content
        );
    }

    public function test_empty_sanitized_content_is_rejected(): void
    {
        $unit =
            $this->createUnit();

        $response =
            $this->post(
                route(
                    'admin.agendas.store'
                ),
                $this->payload(
                    $unit,
                    [
                        'content' =>
                            '<script>alert(1)</script>',
                    ]
                )
            );

        $response->assertSessionHasErrors(
            'content'
        );
    }

    public function test_excerpt_is_generated_automatically(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'excerpt' => null,

                    'content' =>
                        '<p>Agenda pembinaan pegawai untuk peningkatan kualitas layanan.</p>',
                ]
            )
        );

        $agenda =
            Agenda::firstOrFail();

        $this->assertStringContainsString(
            'Agenda pembinaan pegawai',
            $agenda->excerpt
        );
    }

    public function test_published_agenda_without_date_gets_publication_date(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
                [
                    'status' =>
                        'published',

                    'published_at' =>
                        null,
                ]
            )
        );

        $agenda =
            Agenda::firstOrFail();

        $this->assertNotNull(
            $agenda->published_at
        );

        $this->assertSame(
            'published',
            $agenda->publication_state
        );
    }

    public function test_future_publication_is_scheduled(): void
    {
        $unit =
            $this->createUnit();

        $this->post(
            route(
                'admin.agendas.store'
            ),
            $this->payload(
                $unit,
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

        $agenda =
            Agenda::firstOrFail();

        $this->assertSame(
            'scheduled',
            $agenda->publication_state
        );

        $this->assertFalse(
            Agenda::published()
                ->whereKey(
                    $agenda->id
                )
                ->exists()
        );
    }

    public function test_upcoming_agenda_state(): void
    {
        $unit =
            $this->createUnit();

        $agenda =
            Agenda::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Agenda Akan Datang',

                'slug' =>
                    'agenda-akan-datang',

                'content' =>
                    '<p>Agenda</p>',

                'start_at' =>
                    now()->addDay(),

                'end_at' =>
                    now()->addDay()->addHour(),

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $this->assertSame(
            'upcoming',
            $agenda->event_state
        );
    }

    public function test_ongoing_agenda_state(): void
    {
        $unit =
            $this->createUnit();

        $agenda =
            Agenda::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Agenda Berlangsung',

                'slug' =>
                    'agenda-berlangsung',

                'content' =>
                    '<p>Agenda</p>',

                'start_at' =>
                    now()->subHour(),

                'end_at' =>
                    now()->addHour(),

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $this->assertSame(
            'ongoing',
            $agenda->event_state
        );
    }

    public function test_finished_agenda_state(): void
    {
        $unit =
            $this->createUnit();

        $agenda =
            Agenda::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Agenda Selesai',

                'slug' =>
                    'agenda-selesai',

                'content' =>
                    '<p>Agenda</p>',

                'start_at' =>
                    now()->subDays(2),

                'end_at' =>
                    now()->subDay(),

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $this->assertSame(
            'finished',
            $agenda->event_state
        );
    }

    public function test_agenda_can_be_updated(): void
    {
        $unit =
            $this->createUnit();

        $agenda =
            Agenda::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Judul Lama',

                'slug' =>
                    'judul-lama',

                'content' =>
                    '<p>Lama</p>',

                'start_at' =>
                    now()->addDay(),

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $response =
            $this->put(
                route(
                    'admin.agendas.update',
                    $agenda
                ),
                $this->payload(
                    $unit,
                    [
                        'title' =>
                            'Judul Baru',

                        'content' =>
                            '<p>Konten baru</p>',
                    ]
                )
            );

        $response->assertRedirect(
            route(
                'admin.agendas.index'
            )
        );

        $agenda->refresh();

        $this->assertSame(
            'Judul Baru',
            $agenda->title
        );

        $this->assertSame(
            'judul-baru',
            $agenda->slug
        );
    }

    public function test_agenda_can_be_deleted(): void
    {
        $unit =
            $this->createUnit();

        $agenda =
            Agenda::create([
                'unit_id' =>
                    $unit->id,

                'title' =>
                    'Agenda Hapus',

                'slug' =>
                    'agenda-hapus',

                'content' =>
                    '<p>Hapus</p>',

                'start_at' =>
                    now()->addDay(),

                'status' =>
                    'draft',

                'is_featured' =>
                    false,
            ]);

        $response =
            $this->delete(
                route(
                    'admin.agendas.destroy',
                    $agenda
                )
            );

        $response->assertRedirect(
            route(
                'admin.agendas.index'
            )
        );

        $this->assertSoftDeleted(
            'agendas',
            [
                'id' =>
                    $agenda->id,
            ]
        );
    }

    public function test_index_can_search_by_title(): void
    {
        $unit =
            $this->createUnit();

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Rapat Haji',

            'slug' =>
                'rapat-haji',

            'content' =>
                '<p>Haji</p>',

            'start_at' =>
                now()->addDay(),

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Rapat Kepegawaian',

            'slug' =>
                'rapat-kepegawaian',

            'content' =>
                '<p>Pegawai</p>',

            'start_at' =>
                now()->addDay(),

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.agendas.index',
                    [
                        'search' =>
                            'Haji',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'agendas',
            function ($agendas) {
                return
                    $agendas->total() === 1
                    && $agendas
                        ->first()
                        ->title ===
                        'Rapat Haji';
            }
        );
    }

    public function test_index_can_filter_upcoming_agendas(): void
    {
        $unit =
            $this->createUnit();

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Upcoming',

            'slug' =>
                'upcoming',

            'content' =>
                '<p>Upcoming</p>',

            'start_at' =>
                now()->addDay(),

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Finished',

            'slug' =>
                'finished',

            'content' =>
                '<p>Finished</p>',

            'start_at' =>
                now()->subDays(2),

            'end_at' =>
                now()->subDay(),

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.agendas.index',
                    [
                        'event_state' =>
                            'upcoming',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'agendas',
            function ($agendas) {
                return
                    $agendas->total() === 1
                    && $agendas
                        ->first()
                        ->title ===
                        'Upcoming';
            }
        );
    }

    public function test_index_can_filter_featured_agendas(): void
    {
        $unit =
            $this->createUnit();

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Unggulan',

            'slug' =>
                'unggulan',

            'content' =>
                '<p>Unggulan</p>',

            'start_at' =>
                now()->addDay(),

            'status' =>
                'draft',

            'is_featured' =>
                true,
        ]);

        Agenda::create([
            'unit_id' =>
                $unit->id,

            'title' =>
                'Normal',

            'slug' =>
                'normal',

            'content' =>
                '<p>Normal</p>',

            'start_at' =>
                now()->addDay(),

            'status' =>
                'draft',

            'is_featured' =>
                false,
        ]);

        $response =
            $this->get(
                route(
                    'admin.agendas.index',
                    [
                        'is_featured' =>
                            '1',
                    ]
                )
            );

        $response->assertOk();

        $response->assertViewHas(
            'agendas',
            function ($agendas) {
                return
                    $agendas->total() === 1
                    && $agendas
                        ->first()
                        ->title ===
                        'Unggulan';
            }
        );
    }
}