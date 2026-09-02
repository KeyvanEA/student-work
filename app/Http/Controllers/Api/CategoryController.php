<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->get();

        return response()->json([
            'categories' => $categories,
        ], 200);
    }
}
