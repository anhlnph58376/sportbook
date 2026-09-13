<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => UserStatus::Active,
        ]);

        // Assign selected role (player or venue_owner, defaults to player)
        $roleName = $validated['role'] ?? 'player';
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user->load('roles')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Đăng ký tài khoản thành công.', 201);
    }

    /**
     * Log into an existing account and obtain Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::with('roles')->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse('Email hoặc mật khẩu không chính xác.', 401);
        }

        if ($user->isLocked()) {
            return $this->forbiddenResponse('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }

        $deviceName = $validated['device_name'] ?? 'web';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Đăng nhập thành công.');
    }

    /**
     * Log out current session by revoking active token.
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Đăng xuất thành công.');
    }

    /**
     * Get profile of authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            new UserResource($request->user()->load('roles')),
            'Lấy thông tin tài khoản thành công.'
        );
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->successResponse(
            new UserResource($user->fresh('roles')),
            'Cập nhật hồ sơ thành công.'
        );
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        // Revoke other tokens on password change if using token
        if ($user->currentAccessToken()) {
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        }

        return $this->successResponse(null, 'Đổi mật khẩu thành công.');
    }
}
