<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('review_platform_id')->constrained()->onDelete('cascade');
            $table->string('business_link')->nullable(); // Google Business Link, Facebook Page, etc.
            $table->string('review_link')->nullable(); // Direct review link
            $table->json('additional_data')->nullable(); // Store platform-specific data
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'review_platform_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_platforms');
    }
};