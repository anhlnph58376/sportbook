<?php

namespace App\Notifications;

use App\Models\Venue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VenueApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Venue $venue
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
            ->subject('[SportBook] Cơ sở thể thao của bạn đã được phê duyệt!')
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Chúc mừng! Cơ sở thể thao \"{$this->venue->name}\" của bạn đã được ban quản trị phê duyệt.")
            ->line('Cơ sở thể thao của bạn hiện đã hiển thị công khai và sẵn sàng nhận đơn đặt sân từ các vận động viên.')
            ->action('Quản lý cụm sân', url("/owner/venues/{$this->venue->id}"))
            ->line('Chúc bạn kinh doanh thuận lợi cùng SportBook!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'venue_id' => $this->venue->id,
            'venue_name' => $this->venue->name,
            'slug' => $this->venue->slug,
            'message' => "Cơ sở thể thao \"{$this->venue->name}\" đã được phê duyệt thành công.",
        ];
    }
}
