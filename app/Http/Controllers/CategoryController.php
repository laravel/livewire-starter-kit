<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('documents')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|unique:categories,category_name|max:255'
        ]);

        $category = Category::create([
            'category_name' => $request->category_name
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('documents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'category_name' => 'required|string|unique:categories,category_name,' . $category->id . '|max:255'
        ]);

        $category->update([
            'category_name' => $request->category_name
        ]);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
