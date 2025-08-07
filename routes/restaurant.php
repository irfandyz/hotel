<?php

use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('/restaurants/create', [RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('/restaurants', [RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('/restaurants/{menuItem}/edit', [RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('/restaurants/{menuItem}', [RestaurantController::class, 'update'])->name('restaurants.update');
    Route::post('/restaurants/{menuItem}/image', [RestaurantController::class, 'updateImage'])->name('restaurants.updateImage');
    Route::delete('/restaurants/{menuItem}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');
});
