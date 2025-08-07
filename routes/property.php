<?php

use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::patch('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::patch('/properties/{property}/images', [PropertyController::class, 'updateImages'])->name('properties.update-images');
    Route::post('/properties/{property}/images', [PropertyController::class, 'updateImages'])->name('properties.upload-images');

    Route::delete('/property-images/{id}', [PropertyController::class, 'destroyImage'])->name('property-images.destroy');


});
