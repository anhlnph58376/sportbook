<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VenueService
{
    /**
     * Search and filter venues with pagination and sorting.
     *
     * @param  array<string, mixed>  $filters
     */
    public function searchVenues(array $filters = []): LengthAwarePaginator
    {
        $query = Venue::query()
            ->activeAndApproved()
            ->with(['sports', 'amenities', 'primaryImage', 'courts']);

        // 1. Keyword search (name / description)
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        // 2. Filter by Sport
        if (! empty($filters['sport_id'])) {
            $query->whereHas('sports', function (Builder $q) use ($filters) {
                $q->where('sports.id', $filters['sport_id']);
            });
        } elseif (! empty($filters['sport_slug'])) {
            $query->whereHas('sports', function (Builder $q) use ($filters) {
                $q->where('sports.slug', $filters['sport_slug']);
            });
        }

        // 3. Filter by Province & District
        if (! empty($filters['province'])) {
            $query->where('province', $filters['province']);
        }
        if (! empty($filters['district'])) {
            $query->where('district', $filters['district']);
        }

        // 4. Filter by Minimum Rating
        if (! empty($filters['min_rating'])) {
            $query->where('average_rating', '>=', (float) $filters['min_rating']);
        }

        // 5. Geolocation distance calculation (Haversine formula in km)
        if (! empty($filters['lat']) && ! empty($filters['lng'])) {
            $lat = (float) $filters['lat'];
            $lng = (float) $filters['lng'];

            $query->selectRaw(
                'venues.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            );

            if (! empty($filters['radius_km'])) {
                $query->having('distance', '<=', (float) $filters['radius_km']);
            }
        }

        // 6. Sorting
        $sortBy = $filters['sort_by'] ?? 'rating_desc';
        match ($sortBy) {
            'rating_desc' => $query->orderByDesc('average_rating')->orderByDesc('total_reviews'),
            'distance' => ! empty($filters['lat']) && ! empty($filters['lng'])
                ? $query->orderBy('distance')
                : $query->orderByDesc('average_rating'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('average_rating'),
        };

        $perPage = min((int) ($filters['per_page'] ?? 12), 50);

        return $query->paginate($perPage);
    }
}
