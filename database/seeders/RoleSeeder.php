<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Quản trị viên'],
            ['name' => 'venue_owner', 'display_name' => 'Chủ sân'],
            ['name' => 'player', 'display_name' => 'Khách hàng'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
