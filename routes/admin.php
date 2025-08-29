<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\ReviewPlatformController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Master Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['master.admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Business Management
    Route::resource('businesses', BusinessController::class);
    Route::post('businesses/{business}/toggle-status', [BusinessController::class, 'toggleStatus'])->name('businesses.toggle-status');
    Route::get('businesses/{business}/analytics', [BusinessController::class, 'analytics'])->name('businesses.analytics');
    Route::get('businesses/{business}/qr-code', [BusinessController::class, 'generateQrCode'])->name('businesses.qr-code');
    Route::get('businesses/{business}/reviews', [BusinessController::class, 'reviews'])->name('businesses.reviews');
    
    // Review Platform Management
    Route::resource('platforms', ReviewPlatformController::class);
    Route::post('platforms/{platform}/toggle-status', [ReviewPlatformController::class, 'toggleStatus'])->name('platforms.toggle-status');
    Route::post('platforms/update-order', [ReviewPlatformController::class, 'updateOrder'])->name('platforms.update-order');
    
    // Review Management
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('reviews/{review}/toggle-featured', [ReviewController::class, 'toggleFeatured'])->name('reviews.toggle-featured');
    Route::get('reviews/export/{format}', [ReviewController::class, 'export'])->name('reviews.export');
    
    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/export/{format}', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('analytics/business/{business}', [AnalyticsController::class, 'business'])->name('analytics.business');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('users/{user}/businesses', [UserController::class, 'businesses'])->name('users.businesses');
    Route::get('users/{user}/subscriptions', [UserController::class, 'subscriptions'])->name('users.subscriptions');
    
    // Subscription Management
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::get('subscriptions/export/{format}', [SubscriptionController::class, 'export'])->name('subscriptions.export');
    
    // Plan Management
    Route::resource('plans', PlanController::class);
    Route::post('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    Route::post('plans/{plan}/toggle-popular', [PlanController::class, 'togglePopular'])->name('plans.toggle-popular');
    Route::post('plans/update-order', [PlanController::class, 'updateOrder'])->name('plans.update-order');
    
    // Settings Management
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/chatgpt', [SettingController::class, 'chatgpt'])->name('settings.chatgpt');
    Route::put('settings/chatgpt', [SettingController::class, 'updateChatGpt'])->name('settings.chatgpt.update');
    Route::get('settings/payment', [SettingController::class, 'payment'])->name('settings.payment');
    Route::put('settings/payment', [SettingController::class, 'updatePayment'])->name('settings.payment.update');
    Route::get('settings/upi', [SettingController::class, 'upi'])->name('settings.upi');
    Route::put('settings/upi', [SettingController::class, 'updateUpi'])->name('settings.upi.update');
    
    // Bulk Operations
    Route::post('bulk/reviews/approve', [ReviewController::class, 'bulkApprove'])->name('bulk.reviews.approve');
    Route::delete('bulk/reviews/delete', [ReviewController::class, 'bulkDelete'])->name('bulk.reviews.delete');
    Route::post('bulk/businesses/toggle-status', [BusinessController::class, 'bulkToggleStatus'])->name('bulk.businesses.toggle-status');
    Route::post('bulk/users/toggle-status', [UserController::class, 'bulkToggleStatus'])->name('bulk.users.toggle-status');
    
});

// File Upload Routes (with authentication)
Route::middleware(['auth'])->group(function () {
    Route::post('upload/logo', [App\Http\Controllers\UploadController::class, 'logo'])->name('upload.logo');
    Route::post('upload/platform-icon', [App\Http\Controllers\UploadController::class, 'platformIcon'])->name('upload.platform-icon');
    Route::post('upload/avatar', [App\Http\Controllers\UploadController::class, 'avatar'])->name('upload.avatar');
    Route::delete('upload/{file}', [App\Http\Controllers\UploadController::class, 'delete'])->name('upload.delete');
});