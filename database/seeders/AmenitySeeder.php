<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Bãi đỗ xe máy & ô tô', 'icon' => 'local_parking'],
            ['name' => 'Wifi miễn phí', 'icon' => 'wifi'],
            ['name' => 'Phòng thay đồ & Tắm nóng lạnh', 'icon' => 'shower'],
            ['name' => 'Căng tin / Nước giải khát', 'icon' => 'local_cafe'],
            ['name' => 'Đèn chiếu sáng tiêu chuẩn thi đấu', 'icon' => 'lightbulb'],
            ['name' => 'Cho thuê dụng cụ thi đấu', 'icon' => 'sports'],
            ['name' => 'Khán đài khán giả', 'icon' => 'stadium'],
            ['name' => 'Camera an ninh', 'icon' => 'videocam'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']], $amenity);
        }
    }
}
