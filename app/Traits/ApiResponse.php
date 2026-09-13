<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Send standard success JSON response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Thành công',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Send standard paginated JSON response.
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Thành công'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Send standard error JSON response.
     *
     * @param  array<string, mixed>|null  $errors
     */
    protected function errorResponse(
        string $message = 'Đã có lỗi xảy ra',
        int $status = 400,
        ?array $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Send HTTP 409 Conflict response for race conditions.
     */
    protected function conflictResponse(
        string $message = 'Khung giờ này vừa có người đặt hoặc đang trong quá trình thanh toán. Vui lòng chọn khung giờ khác.'
    ): JsonResponse {
        return $this->errorResponse($message, 409);
    }

    /**
     * Send HTTP 403 Forbidden response.
     */
    protected function forbiddenResponse(
        string $message = 'Bạn không có quyền thực hiện hành động này.'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    /**
     * Send HTTP 404 Not Found response.
     */
    protected function notFoundResponse(
        string $message = 'Không tìm thấy dữ liệu yêu cầu.'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }
}
