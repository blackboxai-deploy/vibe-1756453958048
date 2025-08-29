<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->integer('business_limit'); // Number of businesses allowed
            $table->integer('review_limit_per_month'); // Reviews per month
            $table->integer('analytics_retention_days'); // Days to keep analytics
            $table->json('features'); // Array of features
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default plans
        DB::table('plans')->insert([
            [
                'name' => 'Starter',
                'description' => 'Perfect for small businesses starting with online reviews',
                'price' => 999.00,
                'billing_cycle' => 'monthly',
                'business_limit' => 1,
                'review_limit_per_month' => 100,
                'analytics_retention_days' => 30,
                'features' => json_encode(['Basic Analytics', 'QR Code Generation', 'Google Reviews Integration']),
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Professional',
                'description' => 'Ideal for growing businesses with multiple locations',
                'price' => 2499.00,
                'billing_cycle' => 'monthly',
                'business_limit' => 5,
                'review_limit_per_month' => 500,
                'analytics_retention_days' => 90,
                'features' => json_encode(['Advanced Analytics', 'Multiple Platforms', 'AI Review Suggestions', 'Priority Support']),
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Complete solution for large businesses and agencies',
                'price' => 4999.00,
                'billing_cycle' => 'monthly',
                'business_limit' => -1, // Unlimited
                'review_limit_per_month' => -1, // Unlimited
                'analytics_retention_days' => 365,
                'features' => json_encode(['Unlimited Everything', 'Custom Branding', 'API Access', 'Dedicated Support', 'White Label']),
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};