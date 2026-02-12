<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Category;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('document_name', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->get();
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'document_name' => 'required|string|max:255',
            'url' => 'required|url'
        ]);

        $category = Category::find($request->category_id);

        $document = Document::create([
            'category_id' => $request->category_id,
            'category_name' => $category->category_name,
            'document_name' => $request->document_name,
            'url' => $request->url
        ]);

        return response()->json($document->load('category'), 201);
    }

    public function show(Document $document)
    {
        return response()->json($document->load('category'));
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'document_name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url'
        ]);

        if ($request->has('category_id')) {
            $category = Category::find($request->category_id);
            $document->category_name = $category->category_name;
        }

        $document->update($request->all());

        return response()->json($document->load('category'));
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(null, 204);
    }
}
