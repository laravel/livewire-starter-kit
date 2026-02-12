<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;

class FileTrackerController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $documents = Document::with('category')->get();
        
        return view('filetracker.index', compact('categories', 'documents'));
    }
}
