<?php

namespace Database\Seeders;

use App\Enums\CourtStatus;
use App\Enums\DayType;
use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Models\Amenity;
use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\PricingRule;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $owner1 = User::where('email', 'owner1@sportbook.vn')->first();
        $owner2 = User::where('email', 'owner2@sportbook.vn')->first();

        $soccer = Sport::where('slug', 'bong-da')->first();
        $badminton = Sport::where('slug', 'cau-long')->first();
        $pickleball = Sport::where('slug', 'pickleball')->first();
        $basketball = Sport::where('slug', 'bong-ro')->first();
        $tennis = Sport::where('slug', 'tennis')->first();

        $amenities = Amenity::all();

        // 1. Sân Hoàng Gia (Approved & Active)
        $venue1 = Venue::firstOrCreate(
            ['slug' => 'san-bong-da-cau-long-hoang-gia'],
            [
                'owner_id' => $owner1->id,
                'name' => 'Sân Bóng Đá & Cầu Lông Hoàng Gia',
                'slug' => 'san-bong-da-cau-long-hoang-gia',
                'description' => 'Tổ hợp thể thao cao cấp với cụm sân cỏ nhân tạo chuẩn FIFA và sân cầu lông thảm chuyên dụng thi đấu quốc tế. Hệ thống đèn LED hiện đại không chói mắt.',
                'address' => 'Số 152 Nguyễn Thị Thập',
                'province' => 'Hồ Chí Minh',
                'district' => 'Quận 7',
                'ward' => 'Phường Tân Phú',
                'latitude' => 10.741234,
                'longitude' => 106.723456,
                'phone' => '0908888999',
                'email' => 'hoangia.sport@gmail.com',
                'opening_time' => '06:00:00',
                'closing_time' => '23:00:00',
                'status' => VenueStatus::Active,
                'verification_status' => VenueVerificationStatus::Approved,
                'average_rating' => 4.8,
                'total_reviews' => 2,
            ]
        );

        $venue1->sports()->syncWithoutDetaching([$soccer->id, $badminton->id, $pickleball->id]);
        $venue1->amenities()->syncWithoutDetaching($amenities->pluck('id'));

        // Operating hours: 7 days
        for ($day = 0; $day <= 6; $day++) {
            OperatingHour::updateOrCreate(
                ['venue_id' => $venue1->id, 'day_of_week' => $day],
                [
                    'is_closed' => false,
                    'open_time' => '06:00:00',
                    'close_time' => '23:00:00',
                ]
            );
        }

        // Courts for Venue 1
        $court1 = Court::firstOrCreate(
            ['venue_id' => $venue1->id, 'name' => 'Sân Bóng Đá 7 Người (Sân A)'],
            [
                'sport_id' => $soccer->id,
                'description' => 'Mặt cỏ nhân tạo nhập khẩu Ý, có lưới chắn bóng an toàn.',
                'capacity' => 14,
                'status' => CourtStatus::Active,
            ]
        );

        $court2 = Court::firstOrCreate(
            ['venue_id' => $venue1->id, 'name' => 'Sân Cầu Lông VIP 1'],
            [
                'sport_id' => $badminton->id,
                'description' => 'Thảm cao su chuẩn BWF, đèn chiếu sáng 500 lux.',
                'capacity' => 4,
                'status' => CourtStatus::Active,
            ]
        );

        $court3 = Court::firstOrCreate(
            ['venue_id' => $venue1->id, 'name' => 'Sân Pickleball Pro 1'],
            [
                'sport_id' => $pickleball->id,
                'description' => 'Mặt sân Acrylic giảm chấn thương, bóng thi đấu tiêu chuẩn.',
                'capacity' => 4,
                'status' => CourtStatus::Active,
            ]
        );

        // Pricing rules for Court 1 (Soccer)
        PricingRule::firstOrCreate(
            ['court_id' => $court1->id, 'name' => 'Giờ thường (Sáng - Chiều)'],
            [
                'start_time' => '06:00:00',
                'end_time' => '16:00:00',
                'price_per_hour' => 250000.00,
                'day_type' => DayType::All,
                'is_active' => true,
            ]
        );
        PricingRule::firstOrCreate(
            ['court_id' => $court1->id, 'name' => 'Giờ vàng (Tối)'],
            [
                'start_time' => '16:00:00',
                'end_time' => '23:00:00',
                'price_per_hour' => 400000.00,
                'day_type' => DayType::All,
                'is_active' => true,
            ]
        );

        // Pricing rules for Court 2 (Badminton)
        PricingRule::firstOrCreate(
            ['court_id' => $court2->id, 'name' => 'Giờ tiêu chuẩn Cầu lông'],
            [
                'start_time' => '06:00:00',
                'end_time' => '23:00:00',
                'price_per_hour' => 120000.00,
                'day_type' => DayType::All,
                'is_active' => true,
            ]
        );

        // Pricing rules for Court 3 (Pickleball)
        PricingRule::firstOrCreate(
            ['court_id' => $court3->id, 'name' => 'Giờ tiêu chuẩn Pickleball'],
            [
                'start_time' => '06:00:00',
                'end_time' => '23:00:00',
                'price_per_hour' => 160000.00,
                'day_type' => DayType::All,
                'is_active' => true,
            ]
        );

        // 2. Venue 2: Trung Tâm Thể Thao Đa Năng Tân Bình
        $venue2 = Venue::firstOrCreate(
            ['slug' => 'trung-tam-the-thao-da-nang-tan-binh'],
            [
                'owner_id' => $owner2->id,
                'name' => 'Trung Tâm Thể Thao Đa Năng Tân Bình',
                'slug' => 'trung-tam-the-thao-da-nang-tan-binh',
                'description' => 'Trung tâm phức hợp thể thao bóng rổ, tennis và cầu lông với phòng thay đồ máy lạnh và căng tin giải khát.',
                'address' => 'Số 448 Hoàng Văn Thụ',
                'province' => 'Hồ Chí Minh',
                'district' => 'Quận Tân Bình',
                'ward' => 'Phường 4',
                'latitude' => 10.796543,
                'longitude' => 106.654321,
                'phone' => '0907777888',
                'email' => 'tanbinh.sports@gmail.com',
                'opening_time' => '06:00:00',
                'closing_time' => '22:00:00',
                'status' => VenueStatus::Active,
                'verification_status' => VenueVerificationStatus::Approved,
                'average_rating' => 4.5,
                'total_reviews' => 1,
            ]
        );
        $venue2->sports()->syncWithoutDetaching([$basketball->id, $tennis->id, $badminton->id]);
        $venue2->amenities()->syncWithoutDetaching($amenities->take(5)->pluck('id'));

        for ($day = 0; $day <= 6; $day++) {
            OperatingHour::updateOrCreate(
                ['venue_id' => $venue2->id, 'day_of_week' => $day],
                [
                    'is_closed' => false,
                    'open_time' => '06:00:00',
                    'close_time' => '22:00:00',
                ]
            );
        }

        $court4 = Court::firstOrCreate(
            ['venue_id' => $venue2->id, 'name' => 'Sân Bóng Rổ Trong Nhà'],
            [
                'sport_id' => $basketball->id,
                'description' => 'Sàn gỗ chuyên dụng thi đấu, bảng rổ kính cường lực.',
                'capacity' => 10,
                'status' => CourtStatus::Active,
            ]
        );
        PricingRule::firstOrCreate(
            ['court_id' => $court4->id, 'name' => 'Giá tiêu chuẩn Bóng rổ'],
            [
                'start_time' => '06:00:00',
                'end_time' => '22:00:00',
                'price_per_hour' => 300000.00,
                'day_type' => DayType::All,
                'is_active' => true,
            ]
        );

        // 3. Sân đang chờ Admin duyệt (Pending Review)
        Venue::firstOrCreate(
            ['slug' => 'clb-the-thao-binh-thanh-club'],
            [
                'owner_id' => $owner1->id,
                'name' => 'CLB Thể Thao Bình Thạnh Club',
                'slug' => 'clb-the-thao-binh-thanh-club',
                'description' => 'Cụm sân mới xây dựng đang nộp hồ sơ xin cấp phép hoạt động.',
                'address' => 'Số 88 Điện Biên Phủ',
                'province' => 'Hồ Chí Minh',
                'district' => 'Quận Bình Thạnh',
                'ward' => 'Phường 15',
                'latitude' => 10.801234,
                'longitude' => 106.701234,
                'phone' => '0901112233',
                'email' => 'binhthanh.club@gmail.com',
                'opening_time' => '07:00:00',
                'closing_time' => '22:00:00',
                'status' => VenueStatus::Draft,
                'verification_status' => VenueVerificationStatus::PendingReview,
                'rejection_reason' => null,
            ]
        );

        // 4. Sân bị từ chối (Rejected)
        Venue::firstOrCreate(
            ['slug' => 'san-bong-thieu-giay-phep'],
            [
                'owner_id' => $owner2->id,
                'name' => 'Sân Bóng Đá Tự Phát Bình Chánh',
                'slug' => 'san-bong-thieu-giay-phep',
                'description' => 'Hồ sơ thiếu giấy phép kinh doanh phòng cháy chữa cháy.',
                'address' => 'Ấp 3 Xã Vĩnh Lộc',
                'province' => 'Hồ Chí Minh',
                'district' => 'Huyện Bình Chánh',
                'ward' => 'Xã Vĩnh Lộc A',
                'latitude' => 10.812345,
                'longitude' => 106.551234,
                'phone' => '0904445566',
                'email' => 'binhchanh.unlicensed@gmail.com',
                'opening_time' => '08:00:00',
                'closing_time' => '21:00:00',
                'status' => VenueStatus::Inactive,
                'verification_status' => VenueVerificationStatus::Rejected,
                'rejection_reason' => 'Thiếu chứng nhận PCCC và giấy phép kinh doanh hợp lệ.',
            ]
        );
    }
}
