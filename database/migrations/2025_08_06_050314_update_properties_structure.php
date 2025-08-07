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
        // Hapus tabel yang tidak diperlukan
        Schema::dropIfExists('ownerships');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('restaurant_menu_items');
        Schema::dropIfExists('tv_managers');

        // Update tabel properties untuk fokus pada hotel
        Schema::table('properties', function (Blueprint $table) {
            // Hapus kolom type karena sekarang hanya hotel
            $table->dropColumn('type');

            // Tambah kolom untuk hotel
            $table->integer('star_rating')->nullable()->after('description');
            $table->integer('total_rooms')->nullable()->after('star_rating');
            $table->string('hotel_category')->nullable()->after('total_rooms'); // budget, mid-range, luxury
        });

        // Buat tabel restaurants
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('phone')->nullable();
            $table->string('cuisine_type')->nullable(); // italian, chinese, japanese, etc
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Buat tabel restaurant_menu_items
        Schema::create('restaurant_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('category')->nullable(); // appetizer, main, dessert, beverage
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->timestamps();
        });

        // Buat tabel tv_managers
        Schema::create('tv_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('guest_name');
            $table->string('image')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('room_number')->nullable();
            $table->string('check_in_date')->nullable();
            $table->string('check_out_date')->nullable();
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
            $table->timestamps();
        });

        // Buat tabel property_images
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('image');
            $table->string('caption')->nullable();
            $table->enum('type', ['exterior', 'interior', 'room', 'facility'])->default('interior');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel baru
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('tv_managers');
        Schema::dropIfExists('restaurant_menu_items');
        Schema::dropIfExists('restaurants');

        // Kembalikan tabel properties ke struktur lama
        Schema::table('properties', function (Blueprint $table) {
            $table->string('type')->after('description');
            $table->dropColumn(['star_rating', 'total_rooms', 'hotel_category']);
        });

        // Buat ulang tabel lama
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

        Schema::create('restaurant_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('price')->nullable();
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
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
};
