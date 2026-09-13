<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Venue\ApproveVenueRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\VenueResource;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\VenueApprovedNotification;
use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse;

    /**
     * Get platform overview metrics and statistics.
     */
    public function metrics(): JsonResponse
    {
        $totalRevenue = Payment::where('status', PaymentStatus::Completed)->sum('amount');
        $confirmedBookings = Booking::whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])->count();

        $metrics = [
            'total_users' => User::count(),
            'total_venues' => Venue::count(),
            'active_venues' => Venue::where('status', VenueStatus::Active)->count(),
            'pending_venues' => Venue::where('verification_status', VenueVerificationStatus::PendingReview)->count(),
            'total_courts' => Court::count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => $confirmedBookings,
            'total_revenue_vnd' => (float) $totalRevenue,
        ];

        return $this->successResponse($metrics, 'Lấy số liệu thống kê hệ thống thành công.');
    }

    /**
     * List venues pending approval.
     */
    public function pendingVenues(Request $request): JsonResponse
    {
        $venues = Venue::with(['sports', 'owner'])
            ->where('verification_status', VenueVerificationStatus::PendingReview)
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            VenueResource::collection($venues),
            'Lấy danh sách sân chờ phê duyệt thành công.'
        );
    }

    /**
     * Approve or reject a venue application.
     */
    public function approveVenue(ApproveVenueRequest $request, Venue $venue): JsonResponse
    {
        $action = $request->validated('action');
        $rejectionReason = $request->validated('rejection_reason');
        $oldValues = $venue->only(['status', 'verification_status', 'rejection_reason']);

        DB::transaction(function () use ($venue, $action, $rejectionReason, $oldValues) {
            if ($action === 'approve') {
                $venue->update([
                    'verification_status' => VenueVerificationStatus::Approved,
                    'status' => VenueStatus::Active,
                    'rejection_reason' => null,
                ]);

                AuditLogService::log(
                    action: 'APPROVE_VENUE',
                    auditable: $venue,
                    oldValues: $oldValues,
                    newValues: $venue->only(['status', 'verification_status'])
                );

                if ($venue->owner) {
                    $venue->owner->notify(new VenueApprovedNotification($venue));
                }
            } else {
                $venue->update([
                    'verification_status' => VenueVerificationStatus::Rejected,
                    'status' => VenueStatus::Inactive,
                    'rejection_reason' => $rejectionReason,
                ]);

                AuditLogService::log(
                    action: 'REJECT_VENUE',
                    auditable: $venue,
                    oldValues: $oldValues,
                    newValues: $venue->only(['status', 'verification_status', 'rejection_reason'])
                );
            }
        });

        $message = $action === 'approve'
            ? 'Phê duyệt cơ sở thể thao thành công. Sân đã được kích hoạt công khai.'
            : 'Từ chối đơn đăng ký cơ sở thể thao thành công.';

        return $this->successResponse(
            new VenueResource($venue->fresh()),
            $message
        );
    }

    /**
     * List system users with filter.
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($request->has('role')) {
            $role = $request->query('role');
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $users = $query->latest()->paginate(20);

        return $this->successResponse(
            UserResource::collection($users),
            'Lấy danh sách người dùng thành công.'
        );
    }

    /**
     * Toggle lock / unlock status for a user.
     */
    public function toggleUserLock(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return $this->forbiddenResponse('Không thể khóa tài khoản quản trị viên.');
        }

        $newStatus = $user->isLocked() ? UserStatus::Active : UserStatus::Locked;
        $oldStatus = $user->status;

        $user->update(['status' => $newStatus]);

        if ($newStatus === UserStatus::Locked) {
            $user->tokens()->delete();
        }

        AuditLogService::log(
            action: $newStatus === UserStatus::Locked ? 'LOCK_USER' : 'UNLOCK_USER',
            auditable: $user,
            oldValues: ['status' => $oldStatus->value],
            newValues: ['status' => $newStatus->value]
        );

        $message = $newStatus === UserStatus::Locked
            ? 'Đã khóa tài khoản người dùng thành công.'
            : 'Đã mở khóa tài khoản người dùng thành công.';

        return $this->successResponse(
            new UserResource($user->fresh('roles')),
            $message
        );
    }

    /**
     * View audit logs.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $logs = AuditLog::with('user')->latest()->paginate(30);

        return $this->successResponse($logs, 'Lấy lịch sử thao tác hệ thống thành công.');
    }
}
