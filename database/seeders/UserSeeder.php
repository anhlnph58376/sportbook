<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $ownerRole = Role::where('name', 'venue_owner')->first();
        $playerRole = Role::where('name', 'player')->first();

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sportbook.vn'],
            [
                'name' => 'System Administrator',
                'phone' => '0901234567',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Venue Owners
        $owner1 = User::firstOrCreate(
            ['email' => 'owner1@sportbook.vn'],
            [
                'name' => 'Trần Văn Hoàng (Chủ Sân Hoàng Gia)',
                'phone' => '0908888999',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ]
        );
        $owner1->roles()->syncWithoutDetaching([$ownerRole->id]);

        $owner2 = User::firstOrCreate(
            ['email' => 'owner2@sportbook.vn'],
            [
                'name' => 'Lê Thị Thuỷ (Chủ Cụm Sân Thể Thao Đa Năng)',
                'phone' => '0907777888',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ]
        );
        $owner2->roles()->syncWithoutDetaching([$ownerRole->id]);

        // 3. Players
        $player1 = User::firstOrCreate(
            ['email' => 'player1@sportbook.vn'],
            [
                'name' => 'Nguyễn Tuấn Anh (Cầu thủ phong trào)',
                'phone' => '0912345678',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ]
        );
        $player1->roles()->syncWithoutDetaching([$playerRole->id]);

        $player2 = User::firstOrCreate(
            ['email' => 'player2@sportbook.vn'],
            [
                'name' => 'Vũ Minh Đức (Người chơi)',
                'phone' => '0934567890',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ]
        );
        $player2->roles()->syncWithoutDetaching([$playerRole->id]);

        // 4. Locked user for testing security/auth
        $lockedUser = User::firstOrCreate(
            ['email' => 'locked@sportbook.vn'],
            [
                'name' => 'Người Dùng Bị Khóa',
                'phone' => '0999999999',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Locked,
            ]
        );
        $lockedUser->roles()->syncWithoutDetaching([$playerRole->id]);
    }
}
