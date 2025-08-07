<?php

use App\Http\Controllers\TvManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tv-managers', [TvManagerController::class, 'index'])->name('tv-managers.index');
    Route::get('/tv-managers/create', [TvManagerController::class, 'create'])->name('tv-managers.create');
    Route::post('/tv-managers', [TvManagerController::class, 'store'])->name('tv-managers.store');
    Route::get('/tv-managers/{tvManager}/edit', [TvManagerController::class, 'edit'])->name('tv-managers.edit');
    Route::put('/tv-managers/{tvManager}', [TvManagerController::class, 'update'])->name('tv-managers.update');
    Route::post('/tv-managers/{tvManager}/image', [TvManagerController::class, 'updateImage'])->name('tv-managers.updateImage');
    Route::delete('/tv-managers/{tvManager}', [TvManagerController::class, 'destroy'])->name('tv-managers.destroy');
});
