<?php

namespace Tests\Feature\Admin\Announcement;

use App\Models\Announcement;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementModuleTest extends TestCase
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
            'attachment_media_id' => null,
            'title' => 'Pengumuman Resmi',
            'excerpt' => null,
            'content' => '<p>Isi pengumuman resmi instansi.</p>',
            'status' => 'draft',
            'published_at' => null,
            'expires_at' => null,
            'is_pinned' => '0',
            'meta_title' => null,
            'meta_description' => null,
        ], $overrides);
    }

    public function test_announcement_can_be_created(): void
    {
        $unit = $this->createUnit();

        $response = $this->post(
            route('admin.announcements.store'),
            $this->payload($unit)
        );

        $response->assertRedirect(
            route('admin.announcements.index')
        );

        $this->assertDatabaseHas(
            'announcements',
            [
                'title' => 'Pengumuman Resmi',
                'slug' => 'pengumuman-resmi',
                'unit_id' => $unit->id,
            ]
        );
    }

    public function test_unit_is_required(): void
    {
        $unit = $this->createUnit();

        $response = $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                ['unit_id' => null]
            )
        );

        $response->assertSessionHasErrors(
            'unit_id'
        );
    }

    public function test_slug_is_generated_automatically(): void
    {
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                [
                    'title' =>
                        'Jadwal Pelayanan Idul Fitri',
                ]
            )
        );

        $this->assertDatabaseHas(
            'announcements',
            [
                'slug' =>
                    'jadwal-pelayanan-idul-fitri',
            ]
        );
    }

    public function test_duplicate_slug_gets_suffix(): void
    {
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                ['title' => 'Pendaftaran']
            )
        );

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                ['title' => 'Pendaftaran']
            )
        );

        $this->assertDatabaseHas(
            'announcements',
            ['slug' => 'pendaftaran']
        );

        $this->assertDatabaseHas(
            'announcements',
            ['slug' => 'pendaftaran-2']
        );
    }

    public function test_html_is_sanitized(): void
    {
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                [
                    'content' =>
                        '<p>Aman</p><script>alert(1)</script>',
                ]
            )
        );

        $announcement =
            Announcement::firstOrFail();

        $this->assertStringContainsString(
            '<p>Aman</p>',
            $announcement->content
        );

        $this->assertStringNotContainsString(
            '<script',
            $announcement->content
        );
    }

    public function test_empty_sanitized_content_is_rejected(): void
    {
        $unit = $this->createUnit();

        $response = $this->post(
            route('admin.announcements.store'),
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
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                [
                    'excerpt' => null,
                    'content' =>
                        '<p>Isi lengkap pengumuman untuk ringkasan otomatis.</p>',
                ]
            )
        );

        $announcement =
            Announcement::firstOrFail();

        $this->assertStringContainsString(
            'Isi lengkap pengumuman',
            $announcement->excerpt
        );
    }

    public function test_published_without_date_gets_current_date(): void
    {
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                [
                    'status' => 'published',
                ]
            )
        );

        $announcement =
            Announcement::firstOrFail();

        $this->assertNotNull(
            $announcement->published_at
        );

        $this->assertTrue(
            $announcement->isPublished()
        );
    }

    public function test_future_publication_is_scheduled(): void
    {
        $unit = $this->createUnit();

        $this->post(
            route('admin.announcements.store'),
            $this->payload(
                $unit,
                [
                    'status' => 'published',

                    'published_at' =>
                        now()
                            ->addDay()
                            ->format('Y-m-d H:i:s'),
                ]
            )
        );

        $announcement =
            Announcement::firstOrFail();

        $this->assertSame(
            'scheduled',
            $announcement->publication_state
        );

        $this->assertFalse(
            $announcement->isPublished()
        );
    }

    public function test_expired_announcement_is_not_published(): void
    {
        $unit = $this->createUnit();

        $announcement =
            Announcement::create([
                'unit_id' => $unit->id,
                'title' => 'Expired',
                'slug' => 'expired',
                'content' => '<p>Expired</p>',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'expires_at' => now()->subDay(),
                'is_pinned' => false,
            ]);

        $this->assertSame(
            'expired',
            $announcement->publication_state
        );

        $this->assertFalse(
            $announcement->isPublished()
        );

        $this->assertFalse(
            Announcement::published()
                ->whereKey($announcement->id)
                ->exists()
        );
    }

    public function test_announcement_can_be_updated(): void
    {
        $unit = $this->createUnit();

        $announcement =
            Announcement::create([
                'unit_id' => $unit->id,
                'title' => 'Judul Lama',
                'slug' => 'judul-lama',
                'content' => '<p>Konten lama</p>',
                'status' => 'draft',
                'is_pinned' => false,
            ]);

        $response = $this->put(
            route(
                'admin.announcements.update',
                $announcement
            ),
            $this->payload(
                $unit,
                [
                    'title' => 'Judul Baru',
                    'content' => '<p>Konten baru</p>',
                ]
            )
        );

        $response->assertRedirect(
            route('admin.announcements.index')
        );

        $announcement->refresh();

        $this->assertSame(
            'Judul Baru',
            $announcement->title
        );

        $this->assertSame(
            'judul-baru',
            $announcement->slug
        );
    }

    public function test_announcement_can_be_deleted(): void
    {
        $unit = $this->createUnit();

        $announcement =
            Announcement::create([
                'unit_id' => $unit->id,
                'title' => 'Hapus',
                'slug' => 'hapus',
                'content' => '<p>Hapus</p>',
                'status' => 'draft',
                'is_pinned' => false,
            ]);

        $response = $this->delete(
            route(
                'admin.announcements.destroy',
                $announcement
            )
        );

        $response->assertRedirect(
            route('admin.announcements.index')
        );

        $this->assertSoftDeleted(
            'announcements',
            [
                'id' => $announcement->id,
            ]
        );
    }

    public function test_index_can_search_by_title(): void
    {
        $unit = $this->createUnit();

        Announcement::create([
            'unit_id' => $unit->id,
            'title' => 'Pelayanan Haji',
            'slug' => 'pelayanan-haji',
            'content' => '<p>Haji</p>',
            'status' => 'draft',
            'is_pinned' => false,
        ]);

        Announcement::create([
            'unit_id' => $unit->id,
            'title' => 'Pelayanan Nikah',
            'slug' => 'pelayanan-nikah',
            'content' => '<p>Nikah</p>',
            'status' => 'draft',
            'is_pinned' => false,
        ]);

        $response = $this->get(
            route(
                'admin.announcements.index',
                [
                    'search' => 'Haji',
                ]
            )
        );

        $response->assertOk();

        $response->assertViewHas(
            'announcements',
            function ($announcements) {
                return
                    $announcements->total() === 1
                    && $announcements
                        ->first()
                        ->title === 'Pelayanan Haji';
            }
        );
    }

    public function test_index_can_filter_pinned(): void
    {
        $unit = $this->createUnit();

        Announcement::create([
            'unit_id' => $unit->id,
            'title' => 'Pinned',
            'slug' => 'pinned',
            'content' => '<p>Pinned</p>',
            'status' => 'draft',
            'is_pinned' => true,
        ]);

        Announcement::create([
            'unit_id' => $unit->id,
            'title' => 'Normal',
            'slug' => 'normal',
            'content' => '<p>Normal</p>',
            'status' => 'draft',
            'is_pinned' => false,
        ]);

        $response = $this->get(
            route(
                'admin.announcements.index',
                [
                    'is_pinned' => '1',
                ]
            )
        );

        $response->assertOk();

        $response->assertViewHas(
            'announcements',
            function ($announcements) {
                return
                    $announcements->total() === 1
                    && $announcements
                        ->first()
                        ->title === 'Pinned';
            }
        );
    }
}