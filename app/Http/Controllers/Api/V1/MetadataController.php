<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AmenityResource;
use App\Http\Resources\SportResource;
use App\Models\Amenity;
use App\Models\Sport;
use App\Models\SystemConfiguration;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MetadataController extends Controller
{
    use ApiResponse;

    /**
     * Get active sports list.
     */
    public function sports(): JsonResponse
    {
        $sports = Sport::where('is_active', true)->get();

        return $this->successResponse(
            SportResource::collection($sports),
            'Lấy danh mục môn thể thao thành công.'
        );
    }

    /**
     * Get list of standard venue amenities.
     */
    public function amenities(): JsonResponse
    {
        $amenities = Amenity::all();

        return $this->successResponse(
            AmenityResource::collection($amenities),
            'Lấy danh sách tiện ích thành công.'
        );
    }

    /**
     * Get public platform configuration parameters.
     */
    public function configurations(): JsonResponse
    {
        $keys = [
            'booking_payment_window_minutes',
            'cancellation_full_refund_hours',
            'cancellation_half_refund_hours',
            'default_deposit_percentage',
        ];

        $configs = [];
        foreach ($keys as $key) {
            $configs[$key] = SystemConfiguration::getValue($key);
        }

        return $this->successResponse($configs, 'Lấy cấu hình hệ thống thành công.');
    }
}
