<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BookingConflictException extends Exception
{
    public function __construct(
        string $message = 'Khung giờ này vừa có người đặt hoặc đang trong quá trình thanh toán. Vui lòng chọn khung giờ khác.',
        int $code = 409
    ) {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 409);
    }
}
