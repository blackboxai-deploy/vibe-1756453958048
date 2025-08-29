<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Google, Facebook, TrustPilot, Zomato, Swiggy
            $table->string('icon')->nullable();
            $table->string('color')->default('#000000');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default platforms
        DB::table('review_platforms')->insert([
            ['name' => 'Google', 'icon' => 'google.png', 'color' => '#4285f4', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Facebook', 'icon' => 'facebook.png', 'color' => '#1877f2', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'TrustPilot', 'icon' => 'trustpilot.png', 'color' => '#00b67a', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Zomato', 'icon' => 'zomato.png', 'color' => '#e23744', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Swiggy', 'icon' => 'swiggy.png', 'color' => '#fc8019', 'is_active' => true, 'sort_order' => 5],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('review_platforms');
    }
};