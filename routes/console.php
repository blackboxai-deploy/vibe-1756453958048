<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Custom commands for the Review SAAS application
Artisan::command('review-saas:setup', function () {
    $this->info('Setting up Review SAAS application...');
    
    // Run migrations
    $this->call('migrate');
    
    // Create default admin user
    $this->call('db:seed', ['--class' => 'AdminSeeder']);
    
    // Generate application key
    $this->call('key:generate');
    
    // Create storage links
    $this->call('storage:link');
    
    $this->info('Review SAAS setup completed successfully!');
})->purpose('Setup the Review SAAS application');

Artisan::command('review-saas:check-subscriptions', function () {
    $this->info('Checking expired subscriptions...');
    
    $expiredCount = \App\Models\UserSubscription::where('status', 'active')
        ->where('ends_at', '<', now())
        ->count();
        
    if ($expiredCount > 0) {
        // Mark subscriptions as expired
        \App\Models\UserSubscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);
            
        $this->info("Marked {$expiredCount} subscriptions as expired.");
    } else {
        $this->info('No expired subscriptions found.');
    }
    
    // Check expiring soon
    $expiringSoon = \App\Models\UserSubscription::where('status', 'active')
        ->whereBetween('ends_at', [now(), now()->addDays(7)])
        ->count();
        
    $this->info("{$expiringSoon} subscriptions expiring in the next 7 days.");
    
})->purpose('Check and update expired subscriptions');

Artisan::command('review-saas:generate-qr-codes', function () {
    $this->info('Generating QR codes for businesses...');
    
    $businesses = \App\Models\Business::whereNull('qr_code_path')
        ->orWhere('qr_code_path', '')
        ->get();
        
    $generated = 0;
    
    foreach ($businesses as $business) {
        try {
            $business->generateQrCode();
            $generated++;
            $this->line("Generated QR code for: {$business->business_name}");
        } catch (\Exception $e) {
            $this->error("Failed to generate QR code for {$business->business_name}: {$e->getMessage()}");
        }
    }
    
    $this->info("Generated {$generated} QR codes successfully.");
    
})->purpose('Generate QR codes for businesses that don\'t have them');

Artisan::command('review-saas:cleanup-analytics', function () {
    $days = $this->ask('How many days of analytics to keep?', 90);
    
    $this->info("Cleaning up analytics data older than {$days} days...");
    
    $deleted = \App\Models\Analytics::where('created_at', '<', now()->subDays($days))
        ->delete();
        
    $this->info("Deleted {$deleted} analytics records.");
    
})->purpose('Clean up old analytics data');

Artisan::command('review-saas:send-reminder-emails', function () {
    $this->info('Sending subscription reminder emails...');
    
    // Get subscriptions expiring in 7 days
    $subscriptions = \App\Models\UserSubscription::with('user', 'plan')
        ->where('status', 'active')
        ->whereBetween('ends_at', [now()->addDays(6), now()->addDays(7)])
        ->get();
        
    $sent = 0;
    
    foreach ($subscriptions as $subscription) {
        try {
            // Send reminder email (you would implement this)
            // Mail::to($subscription->user->email)->send(new SubscriptionExpiringMail($subscription));
            $sent++;
            $this->line("Sent reminder to: {$subscription->user->email}");
        } catch (\Exception $e) {
            $this->error("Failed to send reminder to {$subscription->user->email}: {$e->getMessage()}");
        }
    }
    
    $this->info("Sent {$sent} reminder emails successfully.");
    
})->purpose('Send subscription expiration reminder emails');