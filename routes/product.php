<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Products\ProductController;

Route::get('product/list', [ProductController::class, 'list'])->name('product.list');
Route::get('product/create', [ProductController::class, 'create'])->name('product.create');
