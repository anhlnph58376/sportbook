<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Venue\StoreVenueRequest;
use App\Http\Requests\Venue\UpdateVenueRequest;
use App\Http\Resources\VenueDetailResource;
use App\Http\Resources\VenueResource;
use App\Models\OperatingHour;
use App\Models\Venue;
use App\Services\VenueService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenueController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected VenueService $venueService
    ) {}

    /**
     * Search and filter public venues.
     */
    public function index(Request $request): JsonResponse
    {
        $venues = $this->venueService->searchVenues($request->all());

        return $this->successResponse(
            VenueResource::collection($venues),
            'Lấy danh sách địa điểm thành công.',
            200,
            [
                'pagination' => [
                    'current_page' => $venues->currentPage(),
                    'last_page' => $venues->lastPage(),
                    'per_page' => $venues->perPage(),
                    'total' => $venues->total(),
                    'has_more' => $venues->hasMorePages(),
                ],
            ]
        );
    }

    /**
     * View detailed information of a venue.
     */
    public function show(string $slugOrId): JsonResponse
    {
        $venue = Venue::with([
            'sports',
            'amenities',
            'images',
            'operatingHours',
            'courts.sport',
            'courts.pricingRules',
            'courts.images',
        ])
            ->where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        $this->authorize('view', $venue);

        return $this->successResponse(
            new VenueDetailResource($venue),
            'Lấy thông tin chi tiết địa điểm thành công.'
        );
    }

    /**
     * List venues owned by authenticated user.
     */
    public function myVenues(Request $request): JsonResponse
    {
        $venues = Venue::with(['sports', 'amenities', 'primaryImage', 'courts'])
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            VenueResource::collection($venues),
            'Lấy danh sách sân của bạn thành công.',
            200,
            [
                'pagination' => [
                    'current_page' => $venues->currentPage(),
                    'last_page' => $venues->lastPage(),
                    'per_page' => $venues->perPage(),
                    'total' => $venues->total(),
                ],
            ]
        );
    }

    /**
     * Create a new venue application.
     */
    public function store(StoreVenueRequest $request): JsonResponse
    {
        $this->authorize('create', Venue::class);

        $validated = $request->validated();
        $user = $request->user();

        $venue = DB::transaction(function () use ($validated, $user) {
            $slugBase = Str::slug($validated['name']);
            $slug = $slugBase;
            $count = 1;
            while (Venue::where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$count}";
                $count++;
            }

            $venue = Venue::create([
                'owner_id' => $user->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'address' => $validated['address'],
                'province' => $validated['province'],
                'district' => $validated['district'],
                'ward' => $validated['ward'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'opening_time' => $validated['opening_time'],
                'closing_time' => $validated['closing_time'],
                'status' => VenueStatus::Draft,
                'verification_status' => VenueVerificationStatus::PendingReview,
            ]);

            // Sync sports
            if (! empty($validated['sport_ids'])) {
                $venue->sports()->sync($validated['sport_ids']);
            }

            // Sync amenities
            if (! empty($validated['amenity_ids'])) {
                $venue->amenities()->sync($validated['amenity_ids']);
            }

            // Create operating hours
            if (! empty($validated['operating_hours'])) {
                foreach ($validated['operating_hours'] as $hour) {
                    OperatingHour::create([
                        'venue_id' => $venue->id,
                        'day_of_week' => $hour['day_of_week'],
                        'is_closed' => $hour['is_closed'],
                        'open_time' => $hour['open_time'] ?? $validated['opening_time'],
                        'close_time' => $hour['close_time'] ?? $validated['closing_time'],
                    ]);
                }
            } else {
                // Default 7 days standard hours
                for ($day = 0; $day <= 6; $day++) {
                    OperatingHour::create([
                        'venue_id' => $venue->id,
                        'day_of_week' => $day,
                        'is_closed' => false,
                        'open_time' => $validated['opening_time'],
                        'close_time' => $validated['closing_time'],
                    ]);
                }
            }

            return $venue;
        });

        return $this->successResponse(
            new VenueDetailResource($venue->load(['sports', 'amenities', 'operatingHours'])),
            'Tạo hồ sơ cơ sở thể thao thành công. Đang chờ quản trị viên duyệt.',
            201
        );
    }

    /**
     * Update venue details.
     */
    public function update(UpdateVenueRequest $request, Venue $venue): JsonResponse
    {
        $this->authorize('update', $venue);

        $validated = $request->validated();

        $venue->update($validated);

        if (isset($validated['sport_ids'])) {
            $venue->sports()->sync($validated['sport_ids']);
        }

        if (isset($validated['amenity_ids'])) {
            $venue->amenities()->sync($validated['amenity_ids']);
        }

        return $this->successResponse(
            new VenueDetailResource($venue->fresh(['sports', 'amenities', 'operatingHours', 'courts'])),
            'Cập nhật thông tin cơ sở thể thao thành công.'
        );
    }

    /**
     * Soft delete a venue.
     */
    public function destroy(Venue $venue): JsonResponse
    {
        $this->authorize('delete', $venue);

        $venue->delete();

        return $this->successResponse(null, 'Xóa cơ sở thể thao thành công.');
    }
}
