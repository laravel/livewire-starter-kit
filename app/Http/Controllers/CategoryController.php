<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('documents')->ordered()->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|unique:categories,category_name|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50'
        ]);

        $category = Category::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'color' => $request->color ?? '#6B46C1',
            'icon' => $request->icon ?? 'folder'
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
            'category_name' => 'required|string|unique:categories,category_name,' . $category->id . '|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50'
        ]);

        $category->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'color' => $request->color ?? $category->color,
            'icon' => $request->icon ?? $category->icon
        ]);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();
        return response()->json($category);
    }

    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->forceDelete();
        return response()->json(null, 204);
    }

    public function trashed()
    {
        $categories = Category::onlyTrashed()->withCount('documents')->get();
        return response()->json($categories);
    }
}
