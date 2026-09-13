<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CourtController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\MetadataController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\VenueController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ----------------------------------------------------
    // Public Metadata
    // ----------------------------------------------------
    Route::get('/sports', [MetadataController::class, 'sports']);
    Route::get('/amenities', [MetadataController::class, 'amenities']);
    Route::get('/configurations', [MetadataController::class, 'configurations']);

    // ----------------------------------------------------
    // Authentication (Public)
    // ----------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ----------------------------------------------------
    // Public Venue Discovery & Reviews
    // ----------------------------------------------------
    Route::get('/venues', [VenueController::class, 'index']);
    Route::get('/venues/{slugOrId}', [VenueController::class, 'show']);
    Route::get('/venues/{venue}/courts', [CourtController::class, 'index']);
    Route::get('/courts/{court}/availability', [CourtController::class, 'availability']);
    Route::get('/venues/{venue}/reviews', [ReviewController::class, 'index']);

    // ----------------------------------------------------
    // Payment Webhooks & Simulation (Public/Gateway)
    // ----------------------------------------------------
    Route::post('/payments/webhook/{provider}', [PaymentController::class, 'webhook']);
    Route::post('/payments/mock/checkout', [PaymentController::class, 'mockCheckout']);

    // ----------------------------------------------------
    // Authenticated Routes (Player, Owner, Admin)
    // ----------------------------------------------------
    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {

        // Auth management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'changePassword']);
        });

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/venues/{venue}/favorite', [FavoriteController::class, 'toggle']);

        // Bookings (Player actions)
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/my', [BookingController::class, 'myBookings']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::post('/bookings/{booking}/checkout', [PaymentController::class, 'checkout']);

        // Reviews (Player store & report)
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::post('/reviews/{review}/report', [ReviewController::class, 'report']);

        // Notifications (In-app)
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // ----------------------------------------------------
        // Venue Owner Routes (role: venue_owner, admin)
        // ----------------------------------------------------
        Route::middleware('role:venue_owner,admin')->prefix('owner')->group(function () {
            Route::get('/venues', [VenueController::class, 'myVenues']);
            Route::post('/venues', [VenueController::class, 'store']);
            Route::put('/venues/{venue}', [VenueController::class, 'update']);
            Route::delete('/venues/{venue}', [VenueController::class, 'destroy']);

            Route::post('/venues/{venue}/courts', [CourtController::class, 'store']);
            Route::put('/courts/{court}', [CourtController::class, 'update']);
            Route::delete('/courts/{court}', [CourtController::class, 'destroy']);

            Route::get('/venues/{venue}/bookings', [BookingController::class, 'venueBookings']);
            Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn']);
            Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
            Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply']);
        });

        // ----------------------------------------------------
        // Administrator Routes (role: admin)
        // ----------------------------------------------------
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/metrics', [AdminController::class, 'metrics']);
            Route::get('/venues/pending', [AdminController::class, 'pendingVenues']);
            Route::post('/venues/{venue}/approve', [AdminController::class, 'approveVenue']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::post('/users/{user}/toggle-lock', [AdminController::class, 'toggleUserLock']);
            Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
        });
    });
});
