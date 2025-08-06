<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function list()
    {
        return Inertia::render('product/list');
    }

    public function create()
    {
        return Inertia::render('product/create');
    }
}
