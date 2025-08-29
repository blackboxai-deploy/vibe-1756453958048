<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->string('subscription_id')->unique(); // Payment gateway subscription ID
            $table->enum('status', ['active', 'inactive', 'cancelled', 'expired', 'pending'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->date('next_billing_date')->nullable();
            $table->string('payment_method')->nullable(); // razorpay, phonepe, paytm, upi
            $table->string('payment_id')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['ends_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};