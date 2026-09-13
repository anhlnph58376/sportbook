<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ApiResponse;

    /**
     * List user's favorite venues.
     */
    public function index(Request $request): JsonResponse
    {
        $venues = $request->user()->favoriteVenues()
            ->with(['sports', 'amenities', 'primaryImage', 'courts'])
            ->paginate(15);

        return $this->successResponse(
            VenueResource::collection($venues),
            'Lấy danh sách địa điểm yêu thích thành công.'
        );
    }

    /**
     * Toggle favorite status for a venue.
     */
    public function toggle(Venue $venue, Request $request): JsonResponse
    {
        $user = $request->user();
        $isFavorited = $user->favoriteVenues()->where('venue_id', $venue->id)->exists();

        if ($isFavorited) {
            $user->favoriteVenues()->detach($venue->id);
            $favorited = false;
            $message = 'Đã xóa khỏi danh sách yêu thích.';
        } else {
            $user->favoriteVenues()->attach($venue->id);
            $favorited = true;
            $message = 'Đã thêm vào danh sách yêu thích.';
        }

        return $this->successResponse([
            'is_favorite' => $favorited,
            'venue_id' => $venue->id,
        ], $message);
    }
}
