<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $root = Unit::create([
            'name' => 'Kantor Kementerian Agama Kabupaten Tuban',
            'short_name' => 'Kemenag Tuban',
            'slug' => Str::slug('Kantor Kementerian Agama Kabupaten Tuban'),
            'code' => 'KEMENAG-TUBAN',
            'type' => 'kankemenag',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Unit::create([
            'parent_id' => $root->id,
            'name' => 'Subbag Tata Usaha',
            'short_name' => 'Subbag TU',
            'slug' => Str::slug('Subbag Tata Usaha'),
            'code' => 'SUBBAG-TU',
            'type' => 'subbag',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Unit::create([
            'parent_id' => $root->id,
            'name' => 'Seksi Bimbingan Masyarakat Islam',
            'short_name' => 'Bimas Islam',
            'slug' => Str::slug('Seksi Bimbingan Masyarakat Islam'),
            'code' => 'BIMAS-ISLAM',
            'type' => 'seksi',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Unit::create([
            'parent_id' => $root->id,
            'name' => 'KUA Kecamatan Tuban',
            'short_name' => 'KUA Tuban',
            'slug' => Str::slug('KUA Kecamatan Tuban'),
            'code' => 'KUA-TUBAN',
            'type' => 'kua',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
