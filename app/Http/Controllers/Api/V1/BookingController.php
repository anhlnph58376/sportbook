<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BookingConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Venue;
use App\Services\BookingService;
use App\Services\CancellationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BookingService $bookingService,
        protected CancellationService $cancellationService
    ) {}

    /**
     * Create a new court booking (concurrency safe).
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user(),
                $request->validated()
            );

            return $this->successResponse(
                new BookingResource($booking),
                'Đặt sân thành công! Vui lòng thanh toán tiền cọc trong vòng 30 phút để giữ chỗ.',
                201
            );
        } catch (BookingConflictException $e) {
            return $this->conflictResponse($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Get bookings of the authenticated player.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $query = Booking::with(['court.venue', 'payment', 'review'])
            ->where('user_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $bookings = $query->latest()->paginate(15);

        return $this->successResponse(
            BookingResource::collection($bookings),
            'Lấy danh sách đơn đặt sân thành công.',
            200,
            [
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                ],
            ]
        );
    }

    /**
     * View a specific booking detail.
     */
    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        return $this->successResponse(
            new BookingResource($booking->load(['court.venue', 'court.sport', 'user', 'payment', 'review'])),
            'Lấy thông tin đơn đặt sân thành công.'
        );
    }

    /**
     * Get all bookings for a venue (Owner or Admin).
     */
    public function venueBookings(Venue $venue, Request $request): JsonResponse
    {
        $this->authorize('update', $venue);

        $query = Booking::with(['court', 'user', 'payment'])
            ->whereHas('court', function ($q) use ($venue) {
                $q->where('venue_id', $venue->id);
            });

        if ($request->has('date')) {
            $query->where('booking_date', $request->query('date'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $bookings = $query->latest()->paginate(20);

        return $this->successResponse(
            BookingResource::collection($bookings),
            'Lấy danh sách đặt sân của cơ sở thành công.'
        );
    }

    /**
     * Cancel a booking with policy-driven refund.
     */
    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $result = $this->cancellationService->cancelBooking(
                $booking,
                $request->validated('reason')
            );

            return $this->successResponse([
                'booking' => new BookingResource($result['booking']),
                'refund_amount' => $result['refund_amount'],
                'refund_percentage' => $result['refund_percentage'],
            ], 'Hủy đặt sân thành công.');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Check-in player (Owner or Admin).
     */
    public function checkIn(Booking $booking): JsonResponse
    {
        $this->authorize('checkIn', $booking);

        try {
            $updated = $this->bookingService->checkIn($booking);

            return $this->successResponse(
                new BookingResource($updated->load(['court.venue', 'payment'])),
                'Check-in cho khách hàng thành công.'
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Reject a booking (Owner or Admin).
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('reject', $booking);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->bookingService->reject($booking, $request->input('reason'));

            return $this->successResponse(
                new BookingResource($updated->load(['court.venue', 'payment'])),
                'Từ chối đơn đặt sân thành công.'
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
