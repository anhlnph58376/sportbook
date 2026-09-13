<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['name' => 'Bóng đá', 'icon' => 'sports_soccer'],
            ['name' => 'Cầu lông', 'icon' => 'sports_tennis'],
            ['name' => 'Pickleball', 'icon' => 'sports_pickleball'],
            ['name' => 'Bóng rổ', 'icon' => 'sports_basketball'],
            ['name' => 'Tennis', 'icon' => 'sports_tennis'],
            ['name' => 'Bóng chuyền', 'icon' => 'sports_volleyball'],
            ['name' => 'Bơi lội', 'icon' => 'pool'],
            ['name' => 'Bàn bida', 'icon' => 'sports'],
        ];

        foreach ($sports as $sport) {
            Sport::firstOrCreate(
                ['slug' => Str::slug($sport['name'])],
                [
                    'name' => $sport['name'],
                    'slug' => Str::slug($sport['name']),
                    'icon' => $sport['icon'],
                    'is_active' => true,
                ]
            );
        }
    }
}
