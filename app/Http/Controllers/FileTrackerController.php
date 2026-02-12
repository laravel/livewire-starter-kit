<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;

class FileTrackerController extends Controller
{
    public function index()
    {
        // Get all categories with their documents
        $categories = Category::withCount('documents')
                             ->ordered()
                             ->get();
        
        // Get all documents with their category relationships
        $documents = Document::with('category')->get();
        
        // Structure the data to ensure proper category association
        $categoriesWithDocuments = [];
        
        foreach ($categories as $category) {
            $categoryDocuments = $documents->filter(function($document) use ($category) {
                return $document->category_id === $category->id;
            });
            
            $categoriesWithDocuments[] = [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'color' => $category->color,
                'icon' => $category->icon,
                'documents_count' => $category->documents_count,
                'documents' => $categoryDocuments->values()->all()
            ];
        }
        
        // Also include documents without category (if any)
        $uncategorizedDocuments = $documents->filter(function($document) {
            return is_null($document->category_id);
        });
        
        return view('filetracker.index', [
            'categories' => $categories,
            'documents' => $documents,
            'categoriesWithDocuments' => $categoriesWithDocuments,
            'uncategorizedDocuments' => $uncategorizedDocuments
        ]);
    }
}
