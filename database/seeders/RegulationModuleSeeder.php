<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\RegulationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegulationModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Peraturan Menteri Agama',
            'Keputusan Menteri Agama',
            'Surat Edaran',
            'Keputusan Kepala Kantor',
            'Pedoman',
        ] as $index => $name) {
            RegulationType::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        $exists = MenuItem::where('location', 'header')
            ->where(fn ($query) =>
                $query->where('route_name', 'regulations.index')
                    ->orWhere('url', '/regulasi')
            )->exists();

        if (!$exists) {
            MenuItem::create([
                'label' => 'Regulasi',
                'location' => 'header',
                'type' => 'route',
                'route_name' => 'regulations.index',
                'sort_order' => (
                    MenuItem::where('location', 'header')->max('sort_order') ?? 0
                ) + 1,
                'is_active' => true,
                'open_in_new_tab' => false,
            ]);
        }
    }
}
