<?php

use App\Http\Controllers\UserAdmin\DashboardController;
use App\Http\Controllers\UserAdmin\BusinessController;
use App\Http\Controllers\UserAdmin\ReviewController;
use App\Http\Controllers\UserAdmin\AnalyticsController;
use App\Http\Controllers\UserAdmin\SettingController;
use App\Http\Controllers\UserAdmin\SubscriptionController;
use App\Http\Controllers\UserAdmin\ProfileController;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['user.admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Business Management (User's own businesses only)
    Route::resource('businesses', BusinessController::class);
    Route::post('businesses/{business}/toggle-status', [BusinessController::class, 'toggleStatus'])->name('businesses.toggle-status');
    Route::get('businesses/{business}/analytics', [BusinessController::class, 'analytics'])->name('businesses.analytics');
    Route::get('businesses/{business}/qr-code', [BusinessController::class, 'generateQrCode'])->name('businesses.qr-code');
    Route::get('businesses/{business}/reviews', [BusinessController::class, 'reviews'])->name('businesses.reviews');
    Route::post('businesses/{business}/platforms', [BusinessController::class, 'updatePlatforms'])->name('businesses.platforms');
    
    // Review Management (User's business reviews only)
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('reviews/{review}/toggle-featured', [ReviewController::class, 'toggleFeatured'])->name('reviews.toggle-featured');
    Route::get('reviews/export/{format}', [ReviewController::class, 'export'])->name('reviews.export');
    
    // Analytics (User's businesses only)
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/business/{business}', [AnalyticsController::class, 'business'])->name('analytics.business');
    Route::get('analytics/export/{format}', [AnalyticsController::class, 'export'])->name('analytics.export');
    
    // Profile Management
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    
    // Settings (User-specific)
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/chatgpt', [SettingController::class, 'chatgpt'])->name('settings.chatgpt');
    Route::put('settings/chatgpt', [SettingController::class, 'updateChatGpt'])->name('settings.chatgpt.update');
    
    // Subscription Management
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription');
    Route::get('subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('subscription/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('subscription/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::get('subscription/history', [SubscriptionController::class, 'history'])->name('subscription.history');
    Route::get('subscription/invoices', [SubscriptionController::class, 'invoices'])->name('subscription.invoices');
    Route::get('subscription/invoice/{subscription}/download', [SubscriptionController::class, 'downloadInvoice'])->name('subscription.invoice.download');
    
});

// Payment Routes (accessible without subscription check)
Route::middleware(['auth', 'role:user_admin,master_admin'])->group(function () {
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::post('create/{plan}', [PaymentController::class, 'create'])->name('create');
        Route::get('success', [PaymentController::class, 'success'])->name('success');
        Route::get('failed', [PaymentController::class, 'failed'])->name('failed');
        Route::post('verify', [PaymentController::class, 'verify'])->name('verify');
        
        // Payment method specific routes
        Route::post('razorpay/create', [PaymentController::class, 'createRazorpay'])->name('razorpay.create');
        Route::post('phonepe/create', [PaymentController::class, 'createPhonePe'])->name('phonepe.create');
        Route::post('paytm/create', [PaymentController::class, 'createPaytm'])->name('paytm.create');
        Route::post('upi/create', [PaymentController::class, 'createUPI'])->name('upi.create');
        
        // Payment status routes
        Route::get('status/{paymentId}', [PaymentController::class, 'status'])->name('status');
    });
});

// API Routes for User Admin AJAX calls
Route::prefix('api')->middleware(['auth', 'user.admin'])->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
    Route::get('businesses/{business}/analytics/chart', [AnalyticsController::class, 'getChartData'])->name('api.analytics.chart');
    Route::get('businesses/{business}/reviews/latest', [ReviewController::class, 'getLatestReviews'])->name('api.reviews.latest');
    Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('api.reviews.approve');
    Route::get('subscription/usage', [SubscriptionController::class, 'getUsageStats'])->name('api.subscription.usage');
});