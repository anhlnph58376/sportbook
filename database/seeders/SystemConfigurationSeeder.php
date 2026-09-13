<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            [
                'key' => 'booking_payment_window_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Thời gian tối đa để thanh toán tiền cọc trước khi booking bị hủy tự động (phút)',
            ],
            [
                'key' => 'cancellation_full_refund_hours',
                'value' => '24',
                'type' => 'integer',
                'description' => 'Thời gian tối thiểu trước giờ đá để được hoàn 100% tiền cọc (giờ)',
            ],
            [
                'key' => 'cancellation_half_refund_hours',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Thời gian tối thiểu trước giờ đá để được hoàn 50% tiền cọc (giờ)',
            ],
            [
                'key' => 'default_deposit_percentage',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Tỷ lệ đặt cọc mặc định (%)',
            ],
            [
                'key' => 'min_booking_duration_minutes',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Thời lượng đặt sân tối thiểu (phút)',
            ],
            [
                'key' => 'platform_service_fee_percentage',
                'value' => '5',
                'type' => 'decimal',
                'description' => 'Phí hoa hồng nền tảng trên mỗi booking thành công (%)',
            ],
        ];

        foreach ($configs as $config) {
            SystemConfiguration::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
