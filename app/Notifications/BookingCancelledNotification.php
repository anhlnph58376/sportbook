<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public float $refundAmount = 0.0
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
        $message = (new MailMessage)
            ->subject("[SportBook] Đơn đặt sân đã bị hủy — Mã: {$this->booking->booking_code}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Đơn đặt sân mã {$this->booking->booking_code} đã bị hủy.")
            ->line("Lý do: {$this->booking->cancellation_reason}");

        if ($this->refundAmount > 0) {
            $message->line('Số tiền hoàn trả: '.number_format($this->refundAmount).' VND (theo chính sách hủy sân).');
        }

        return $message->line('Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ ban quản trị.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'refund_amount' => $this->refundAmount,
            'reason' => $this->booking->cancellation_reason,
            'message' => "Đơn đặt sân {$this->booking->booking_code} đã được hủy.",
        ];
    }
}
