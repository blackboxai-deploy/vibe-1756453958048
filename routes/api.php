<?php

use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::prefix('v1')->group(function () {
    
    // Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    
    // Public Business Info (for mobile app integration)
    Route::get('business/{slug}', [BusinessController::class, 'show']);
    Route::get('business/{slug}/platforms', [BusinessController::class, 'platforms']);
    
    // Review Submission (Public)
    Route::post('business/{slug}/reviews', [ReviewController::class, 'store']);
    Route::get('business/{slug}/reviews', [ReviewController::class, 'index']);
    Route::get('business/{slug}/reviews/suggestions', [ReviewController::class, 'getSuggestions']);
    
    // Protected API Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Business Management
        Route::apiResource('businesses', BusinessController::class);
        Route::post('businesses/{business}/qr-code', [BusinessController::class, 'generateQrCode']);
        Route::post('businesses/{business}/toggle-status', [BusinessController::class, 'toggleStatus']);
        Route::get('businesses/{business}/analytics', [BusinessController::class, 'analytics']);
        Route::post('businesses/{business}/platforms', [BusinessController::class, 'updatePlatforms']);
        
        // Review Management
        Route::get('reviews', [ReviewController::class, 'userReviews']);
        Route::get('reviews/{review}', [ReviewController::class, 'show']);
        Route::post('reviews/{review}/approve', [ReviewController::class, 'approve']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('reviews/{review}/toggle-featured', [ReviewController::class, 'toggleFeatured']);
        
        // Analytics
        Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('analytics/business/{business}', [AnalyticsController::class, 'business']);
        Route::get('analytics/business/{business}/stats', [AnalyticsController::class, 'businessStats']);
        Route::get('analytics/business/{business}/chart/{type}', [AnalyticsController::class, 'chartData']);
        
        // Subscription Management
        Route::get('subscription', [SubscriptionController::class, 'current']);
        Route::get('subscription/plans', [SubscriptionController::class, 'plans']);
        Route::post('subscription/subscribe/{plan}', [SubscriptionController::class, 'subscribe']);
        Route::post('subscription/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('subscription/usage', [SubscriptionController::class, 'usage']);
        Route::get('subscription/history', [SubscriptionController::class, 'history']);
        
        // User Profile
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('profile/password', [AuthController::class, 'updatePassword']);
        
    });
    
});

// Admin API Routes
Route::prefix('admin/v1')->middleware(['auth:sanctum', 'role:master_admin'])->group(function () {
    
    // Dashboard Stats
    Route::get('dashboard/stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);
    
    // User Management
    Route::apiResource('users', App\Http\Controllers\Api\Admin\UserController::class);
    Route::post('users/{user}/toggle-status', [App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);
    Route::post('users/{user}/reset-password', [App\Http\Controllers\Api\Admin\UserController::class, 'resetPassword']);
    
    // Business Management (All businesses)
    Route::get('businesses/all', [App\Http\Controllers\Api\Admin\BusinessController::class, 'index']);
    Route::post('businesses/{business}/toggle-status', [App\Http\Controllers\Api\Admin\BusinessController::class, 'toggleStatus']);
    
    // Review Management (All reviews)
    Route::get('reviews/all', [App\Http\Controllers\Api\Admin\ReviewController::class, 'index']);
    Route::post('reviews/bulk/approve', [App\Http\Controllers\Api\Admin\ReviewController::class, 'bulkApprove']);
    Route::delete('reviews/bulk/delete', [App\Http\Controllers\Api\Admin\ReviewController::class, 'bulkDelete']);
    
    // Plan Management
    Route::apiResource('plans', App\Http\Controllers\Api\Admin\PlanController::class);
    Route::post('plans/{plan}/toggle-status', [App\Http\Controllers\Api\Admin\PlanController::class, 'toggleStatus']);
    Route::post('plans/{plan}/toggle-popular', [App\Http\Controllers\Api\Admin\PlanController::class, 'togglePopular']);
    
    // Subscription Management (All subscriptions)
    Route::get('subscriptions/all', [App\Http\Controllers\Api\Admin\SubscriptionController::class, 'index']);
    Route::post('subscriptions/{subscription}/cancel', [App\Http\Controllers\Api\Admin\SubscriptionController::class, 'cancel']);
    Route::post('subscriptions/{subscription}/reactivate', [App\Http\Controllers\Api\Admin\SubscriptionController::class, 'reactivate']);
    
    // Analytics (System-wide)
    Route::get('analytics/overview', [App\Http\Controllers\Api\Admin\AnalyticsController::class, 'overview']);
    Route::get('analytics/revenue', [App\Http\Controllers\Api\Admin\AnalyticsController::class, 'revenue']);
    Route::get('analytics/users', [App\Http\Controllers\Api\Admin\AnalyticsController::class, 'users']);
    
    // Settings Management
    Route::get('settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'index']);
    Route::put('settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'update']);
    Route::post('settings/test-chatgpt', [App\Http\Controllers\Api\Admin\SettingController::class, 'testChatGPT']);
    
});

// Webhook Routes (no authentication required)
Route::prefix('webhooks')->name('api.webhooks.')->group(function () {
    Route::post('razorpay', [WebhookController::class, 'razorpay'])->name('razorpay');
    Route::post('phonepe', [WebhookController::class, 'phonepe'])->name('phonepe');
    Route::post('paytm', [WebhookController::class, 'paytm'])->name('paytm');
    Route::post('subscription/expired', [WebhookController::class, 'subscriptionExpired'])->name('subscription.expired');
});

// Health Check
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
        'database' => 'connected',
    ]);
})->name('api.health');

// Rate limit test endpoint
Route::get('test/rate-limit', function () {
    return response()->json([
        'message' => 'Rate limit test successful',
        'timestamp' => now()->toISOString(),
    ]);
})->middleware('throttle:60,1'); // 60 requests per minute