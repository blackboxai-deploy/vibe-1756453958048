<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\QRController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Frontend Review Routes (Public)
Route::prefix('review')->name('review.')->group(function () {
    Route::get('/{slug}', [ReviewController::class, 'show'])->name('show');
    Route::post('/{slug}', [ReviewController::class, 'store'])->name('store');
    Route::get('/{slug}/suggestions', [ReviewController::class, 'getSuggestions'])->name('suggestions');
    Route::get('/{slug}/redirect/{platform?}', [ReviewController::class, 'redirect'])->name('redirect');
});

// QR Code Routes
Route::prefix('qr')->name('qr.')->group(function () {
    Route::get('/{slug}', [QRController::class, 'scan'])->name('scan');
    Route::get('/{slug}/download', [QRController::class, 'download'])->name('download');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if ($user->isMasterAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('user-admin.dashboard');
    })->name('dashboard');
    
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// API Routes for AJAX calls
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/analytics/{business}/stats', [App\Http\Controllers\Api\AnalyticsController::class, 'getStats']);
    Route::get('/reviews/{business}/latest', [App\Http\Controllers\Api\ReviewController::class, 'getLatest']);
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\Api\ReviewController::class, 'approve']);
    Route::delete('/reviews/{review}', [App\Http\Controllers\Api\ReviewController::class, 'destroy']);
});

// Payment webhook routes (outside auth middleware)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/razorpay', [App\Http\Controllers\Payment\RazorpayController::class, 'webhook'])->name('razorpay');
    Route::post('/phonepe', [App\Http\Controllers\Payment\PhonePeController::class, 'webhook'])->name('phonepe');
    Route::post('/paytm', [App\Http\Controllers\Payment\PaytmController::class, 'webhook'])->name('paytm');
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
    ]);
})->name('health');