<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Initiate deposit payment checkout for a booking.
     */
    public function checkout(Booking $booking, Request $request): JsonResponse
    {
        $this->authorize('view', $booking);

        $provider = $request->input('provider', 'mock');

        try {
            $checkoutData = $this->paymentService->initiateBookingPayment($booking, $provider);

            return $this->successResponse(
                $checkoutData,
                'Khởi tạo phiên thanh toán thành công.'
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Process payment webhook from gateway.
     */
    public function webhook(string $provider, Request $request): JsonResponse
    {
        $signature = $request->header('X-Signature') ?? $request->header('Signature');

        try {
            $result = $this->paymentService->handleWebhook(
                $provider,
                $request->all(),
                $signature
            );

            return $this->successResponse([
                'booking' => $result['booking'] ? new BookingResource($result['booking']) : null,
            ], $result['message']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Simulated mock payment endpoint for testing and live demonstrations.
     */
    public function mockCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'status' => ['nullable', 'string', 'in:success,failed'],
        ]);

        $status = $request->input('status', 'success');

        $result = $this->paymentService->handleWebhook('mock', [
            'payment_id' => $request->input('payment_id'),
            'status' => $status,
            'transaction_id' => 'MOCK-'.strtoupper(uniqid()),
        ]);

        return $this->successResponse([
            'booking' => $result['booking'] ? new BookingResource($result['booking']) : null,
        ], $result['message']);
    }
}
