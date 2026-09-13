<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $player1 = User::where('email', 'player1@sportbook.vn')->first();
        $player2 = User::where('email', 'player2@sportbook.vn')->first();
        $owner1 = User::where('email', 'owner1@sportbook.vn')->first();

        $court1 = Court::where('name', 'Sân Bóng Đá 7 Người (Sân A)')->first();
        $court2 = Court::where('name', 'Sân Cầu Lông VIP 1')->first();

        if (! $court1 || ! $player1) {
            return;
        }

        // 1. Completed Booking with 5-star Review
        $booking1 = Booking::firstOrCreate(
            ['booking_code' => 'SB-20260910-0001'],
            [
                'user_id' => $player1->id,
                'court_id' => $court1->id,
                'booking_code' => 'SB-20260910-0001',
                'booking_date' => now()->subDays(3)->format('Y-m-d'),
                'start_time' => '17:00:00',
                'end_time' => '19:00:00',
                'duration_hours' => 2.0,
                'total_price' => 800000.00,
                'deposit_amount' => 240000.00,
                'deposit_percentage' => 30,
                'status' => BookingStatus::Completed,
                'notes' => 'Trận giao hữu công ty FPT',
            ]
        );

        Payment::firstOrCreate(
            ['booking_id' => $booking1->id],
            [
                'transaction_id' => 'TXN-MOMO-'.Str::random(10),
                'amount' => 240000.00,
                'payment_method' => 'momo',
                'status' => PaymentStatus::Completed,
                'paid_at' => now()->subDays(4),
                'provider_response' => ['resultCode' => 0, 'message' => 'Giao dịch thành công'],
            ]
        );

        $review1 = Review::firstOrCreate(
            ['booking_id' => $booking1->id],
            [
                'user_id' => $player1->id,
                'venue_id' => $court1->venue_id,
                'rating' => 5,
                'comment' => 'Sân cỏ rất mới và êm chân, hệ thống đèn chiếu sáng cực tốt. Nhân viên hỗ trợ nhiệt tình nước uống và bóng tập!',
                'is_visible' => true,
            ]
        );

        ReviewReply::firstOrCreate(
            ['review_id' => $review1->id],
            [
                'user_id' => $owner1->id,
                'comment' => 'Cảm ơn bạn Tuấn Anh đã tin tưởng và ủng hộ Sân Hoàng Gia. Hẹn gặp lại bạn ở các trận cầu tiếp theo!',
            ]
        );

        // 2. Confirmed Booking (Future date)
        $booking2 = Booking::firstOrCreate(
            ['booking_code' => 'SB-20260920-0002'],
            [
                'user_id' => $player2->id,
                'court_id' => $court2->id,
                'booking_code' => 'SB-20260920-0002',
                'booking_date' => now()->addDays(2)->format('Y-m-d'),
                'start_time' => '18:00:00',
                'end_time' => '20:00:00',
                'duration_hours' => 2.0,
                'total_price' => 240000.00,
                'deposit_amount' => 72000.00,
                'deposit_percentage' => 30,
                'status' => BookingStatus::Confirmed,
            ]
        );

        Payment::firstOrCreate(
            ['booking_id' => $booking2->id],
            [
                'transaction_id' => 'TXN-VNPAY-'.Str::random(10),
                'amount' => 72000.00,
                'payment_method' => 'vnpay',
                'status' => PaymentStatus::Completed,
                'paid_at' => now()->subHours(2),
                'provider_response' => ['vnp_ResponseCode' => '00', 'message' => 'Success'],
            ]
        );

        // 3. Awaiting Payment Booking (Expiring soon)
        Booking::firstOrCreate(
            ['booking_code' => 'SB-20260921-0003'],
            [
                'user_id' => $player1->id,
                'court_id' => $court1->id,
                'booking_code' => 'SB-20260921-0003',
                'booking_date' => now()->addDays(3)->format('Y-m-d'),
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'duration_hours' => 2.0,
                'total_price' => 500000.00,
                'deposit_amount' => 150000.00,
                'deposit_percentage' => 30,
                'status' => BookingStatus::AwaitingPayment,
                'expires_at' => now()->addMinutes(20),
            ]
        );

        // Recalculate venue rating
        $court1->venue->recalculateRating();
    }
}
