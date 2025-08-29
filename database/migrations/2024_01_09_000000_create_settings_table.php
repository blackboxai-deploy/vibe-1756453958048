<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed in frontend
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            // OpenAI Settings
            ['key' => 'openai_api_key', 'value' => '', 'type' => 'string', 'description' => 'OpenAI API Key for ChatGPT integration', 'is_public' => false],
            ['key' => 'openai_model', 'value' => 'gpt-3.5-turbo', 'type' => 'string', 'description' => 'OpenAI model to use', 'is_public' => false],
            ['key' => 'review_suggestions_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable AI review suggestions', 'is_public' => false],
            
            // Payment Gateway Settings
            ['key' => 'razorpay_key_id', 'value' => '', 'type' => 'string', 'description' => 'Razorpay Key ID', 'is_public' => false],
            ['key' => 'razorpay_key_secret', 'value' => '', 'type' => 'string', 'description' => 'Razorpay Key Secret', 'is_public' => false],
            ['key' => 'phonepe_merchant_id', 'value' => '', 'type' => 'string', 'description' => 'PhonePe Merchant ID', 'is_public' => false],
            ['key' => 'phonepe_salt_key', 'value' => '', 'type' => 'string', 'description' => 'PhonePe Salt Key', 'is_public' => false],
            ['key' => 'paytm_merchant_id', 'value' => '', 'type' => 'string', 'description' => 'Paytm Merchant ID', 'is_public' => false],
            ['key' => 'paytm_merchant_key', 'value' => '', 'type' => 'string', 'description' => 'Paytm Merchant Key', 'is_public' => false],
            
            // UPI Settings
            ['key' => 'upi_id', 'value' => '', 'type' => 'string', 'description' => 'UPI ID for payments', 'is_public' => true],
            ['key' => 'upi_merchant_name', 'value' => 'Review SAAS', 'type' => 'string', 'description' => 'UPI Merchant Name', 'is_public' => true],
            
            // Application Settings
            ['key' => 'app_logo', 'value' => '', 'type' => 'string', 'description' => 'Application Logo', 'is_public' => true],
            ['key' => 'company_name', 'value' => 'Review SAAS', 'type' => 'string', 'description' => 'Company Name', 'is_public' => true],
            ['key' => 'support_email', 'value' => 'support@reviewsaas.com', 'type' => 'string', 'description' => 'Support Email', 'is_public' => true],
            ['key' => 'terms_url', 'value' => '', 'type' => 'string', 'description' => 'Terms & Conditions URL', 'is_public' => true],
            ['key' => 'privacy_url', 'value' => '', 'type' => 'string', 'description' => 'Privacy Policy URL', 'is_public' => true],
            
            // Review Settings
            ['key' => 'min_review_length', 'value' => '10', 'type' => 'integer', 'description' => 'Minimum review text length', 'is_public' => false],
            ['key' => 'max_review_length', 'value' => '500', 'type' => 'integer', 'description' => 'Maximum review text length', 'is_public' => false],
            ['key' => 'auto_approve_reviews', 'value' => '0', 'type' => 'boolean', 'description' => 'Auto approve reviews', 'is_public' => false],
            ['key' => 'redirect_delay', 'value' => '3', 'type' => 'integer', 'description' => 'Redirect delay in seconds for 4-5 star reviews', 'is_public' => false],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};