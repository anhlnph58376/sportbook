<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[SportBook] Đặt sân thành công — Mã: {$this->booking->booking_code}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Đơn đặt sân của bạn tại {$this->booking->court->venue->name} đã được xác nhận thành công.")
            ->line("Sân: {$this->booking->court->name}")
            ->line("Ngày: {$this->booking->booking_date->format('d/m/Y')}")
            ->line('Khung giờ: '.substr($this->booking->start_time, 0, 5).' - '.substr($this->booking->end_time, 0, 5))
            ->line('Tiền cọc đã thanh toán: '.number_format($this->booking->deposit_amount).' VND')
            ->action('Xem chi tiết đơn đặt', url("/bookings/{$this->booking->id}"))
            ->line('Cảm ơn bạn đã lựa chọn SportBook!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'venue_name' => $this->booking->court->venue->name,
            'court_name' => $this->booking->court->name,
            'booking_date' => $this->booking->booking_date->format('Y-m-d'),
            'start_time' => substr($this->booking->start_time, 0, 5),
            'end_time' => substr($this->booking->end_time, 0, 5),
            'message' => "Đơn đặt sân {$this->booking->booking_code} đã được xác nhận thành công.",
        ];
    }
}
