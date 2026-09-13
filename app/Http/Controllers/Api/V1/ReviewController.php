<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReplyReviewRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Models\Venue;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    use ApiResponse;

    /**
     * List visible reviews for a venue.
     */
    public function index(Venue $venue): JsonResponse
    {
        $reviews = $venue->reviews()
            ->visible()
            ->with(['user', 'reply.user', 'images'])
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            ReviewResource::collection($reviews),
            'Lấy danh sách đánh giá thành công.',
            200,
            [
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
            ]
        );
    }

    /**
     * Submit a review for a completed booking.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $booking = Booking::with('court.venue')->findOrFail($validated['booking_id']);

        $this->authorize('create', [Review::class, $booking]);

        $review = DB::transaction(function () use ($validated, $booking, $request) {
            $review = Review::create([
                'booking_id' => $booking->id,
                'user_id' => $request->user()->id,
                'venue_id' => $booking->court->venue_id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'is_visible' => true,
            ]);

            if (! empty($validated['images'])) {
                foreach ($validated['images'] as $imagePath) {
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'path' => $imagePath,
                    ]);
                }
            }

            // Recalculate venue rating and review counts
            $booking->court->venue->recalculateRating();

            return $review;
        });

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'images'])),
            'Đánh giá của bạn đã được ghi nhận!',
            201
        );
    }

    /**
     * Venue owner replies to a review.
     */
    public function reply(ReplyReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('reply', $review);

        $reply = ReviewReply::updateOrCreate(
            ['review_id' => $review->id],
            [
                'user_id' => $request->user()->id,
                'comment' => $request->validated('comment'),
            ]
        );

        return $this->successResponse(
            new ReviewResource($review->fresh(['user', 'reply.user', 'images'])),
            'Đã gửi phản hồi đánh giá thành công.'
        );
    }

    /**
     * Report a review as inappropriate.
     */
    public function report(Review $review): JsonResponse
    {
        $review->increment('reported_count');

        return $this->successResponse(null, 'Đã gửi báo cáo đánh giá vi phạm tới ban quản trị.');
    }
}
