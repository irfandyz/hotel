<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('star_rating')->nullable();
            $table->string('total_rooms')->nullable();
            $table->string('hotel_category')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });

        Schema::create('ownerships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('image');
            $table->timestamps();
        });

        Schema::create('restaurant_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('restaurant_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->timestamps();
        });

        Schema::create('restaurant_category_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_category_id')->constrained('restaurant_categories');
            $table->foreignId('restaurant_menu_item_id')->constrained('restaurant_menu_items');
            $table->timestamps();
        });

        Schema::create('tv_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('guest_name');
            $table->string('image')->nullable();
            $table->string('birth_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tv_managers');
        Schema::dropIfExists('restaurant_category_menu_items');
        Schema::dropIfExists('restaurant_menu_items');
        Schema::dropIfExists('restaurant_categories');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('ownerships');
        Schema::dropIfExists('properties');
    }
};
