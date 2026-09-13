<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_list_notifications_and_mark_as_read(): void
    {
        $player = User::where('email', 'player1@sportbook.vn')->firstOrFail();
        $booking = Booking::firstOrFail();

        // Send a notification to player
        $player->notify(new BookingConfirmedNotification($booking));

        $this->assertEquals(1, $player->unreadNotifications()->count());
        $notificationId = $player->unreadNotifications()->first()->id;

        // 1. List notifications
        $response = $this->actingAs($player, 'sanctum')->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'unread_count' => 1,
                ],
            ]);

        // 2. Mark specific notification as read
        $readResponse = $this->actingAs($player, 'sanctum')
            ->postJson("/api/v1/notifications/{$notificationId}/read");

        $readResponse->assertStatus(200);
        $this->assertEquals(0, $player->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $player = User::where('email', 'player2@sportbook.vn')->firstOrFail();
        $booking = Booking::firstOrFail();

        $player->notify(new BookingConfirmedNotification($booking));
        $player->notify(new BookingConfirmedNotification($booking));

        $this->assertEquals(2, $player->unreadNotifications()->count());

        $response = $this->actingAs($player, 'sanctum')->postJson('/api/v1/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, $player->fresh()->unreadNotifications()->count());
    }
}
