<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('logo')->nullable();
            $table->string('full_name');
            $table->string('business_name');
            $table->text('address');
            $table->string('state');
            $table->string('city');
            $table->string('area');
            $table->string('pincode');
            $table->string('mobile_number');
            $table->string('telephone_number')->nullable();
            $table->string('email');
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->json('business_hours')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('total_scans')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};