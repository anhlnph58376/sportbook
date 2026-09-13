<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CourtStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Court\StoreCourtRequest;
use App\Http\Requests\Court\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Court;
use App\Models\PricingRule;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourtController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * List all courts for a venue.
     */
    public function index(Venue $venue): JsonResponse
    {
        $courts = $venue->courts()->with(['sport', 'pricingRules', 'images'])->get();

        return $this->successResponse(
            CourtResource::collection($courts),
            'Lấy danh sách sân thi đấu thành công.'
        );
    }

    /**
     * Get slot availability and pricing for a court on a date.
     */
    public function availability(Court $court, Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $dateString = $request->query('date');
        $result = $this->availabilityService->getCourtAvailability($court, $dateString);

        return $this->successResponse($result, 'Lấy lịch trống sân thành công.');
    }

    /**
     * Add a new court to a venue.
     */
    public function store(StoreCourtRequest $request, Venue $venue): JsonResponse
    {
        $this->authorize('create', [Court::class, $venue]);

        $validated = $request->validated();

        $court = DB::transaction(function () use ($venue, $validated) {
            $court = Court::create([
                'venue_id' => $venue->id,
                'sport_id' => $validated['sport_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'capacity' => $validated['capacity'] ?? 2,
                'status' => CourtStatus::Active,
            ]);

            if (! empty($validated['pricing_rules'])) {
                foreach ($validated['pricing_rules'] as $rule) {
                    PricingRule::create([
                        'court_id' => $court->id,
                        'name' => $rule['name'] ?? 'Khung giờ',
                        'start_time' => $rule['start_time'],
                        'end_time' => $rule['end_time'],
                        'price_per_hour' => $rule['price_per_hour'],
                        'day_type' => $rule['day_type'] ?? 'all',
                        'is_active' => true,
                    ]);
                }
            }

            return $court;
        });

        return $this->successResponse(
            new CourtResource($court->load(['sport', 'pricingRules'])),
            'Thêm sân thi đấu thành công.',
            201
        );
    }

    /**
     * Update court information.
     */
    public function update(UpdateCourtRequest $request, Court $court): JsonResponse
    {
        $this->authorize('update', $court);

        $court->update($request->validated());

        return $this->successResponse(
            new CourtResource($court->fresh(['sport', 'pricingRules'])),
            'Cập nhật sân thi đấu thành công.'
        );
    }

    /**
     * Soft delete a court.
     */
    public function destroy(Court $court): JsonResponse
    {
        $this->authorize('delete', $court);

        $court->delete();

        return $this->successResponse(null, 'Xóa sân thi đấu thành công.');
    }
}
