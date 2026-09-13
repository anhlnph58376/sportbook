<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VenueDiscoveryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_list_venues_with_pagination(): void
    {
        $response = $this->getJson('/api/v1/venues');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'address',
                        'province',
                        'district',
                        'average_rating',
                        'sports',
                    ],
                ],
                'meta' => [
                    'pagination',
                ],
            ]);
    }

    public function test_can_filter_venues_by_keyword(): void
    {
        $response = $this->getJson('/api/v1/venues?q=Hoàng+Gia');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Sân Bóng Đá & Cầu Lông Hoàng Gia',
            ]);
    }

    public function test_can_filter_venues_by_sport(): void
    {
        $response = $this->getJson('/api/v1/venues?sport_slug=bong-da');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $venue) {
            $sportSlugs = collect($venue['sports'])->pluck('slug')->all();
            $this->assertContains('bong-da', $sportSlugs);
        }
    }

    public function test_can_view_venue_details(): void
    {
        $venue = Venue::where('slug', 'san-bong-da-cau-long-hoang-gia')->firstOrFail();

        $response = $this->getJson('/api/v1/venues/'.$venue->slug);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $venue->id,
                    'slug' => $venue->slug,
                    'name' => $venue->name,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'courts',
                    'operating_hours',
                    'sports',
                    'amenities',
                ],
            ]);
    }

    public function test_can_check_court_availability_slots(): void
    {
        $court = Court::where('name', 'Sân Bóng Đá 7 Người (Sân A)')->firstOrFail();
        $testDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->getJson("/api/v1/courts/{$court->id}/availability?date={$testDate}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_closed' => false,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'slots' => [
                        '*' => [
                            'start_time',
                            'end_time',
                            'is_available',
                            'price',
                            'deposit',
                        ],
                    ],
                ],
            ]);
    }
}
