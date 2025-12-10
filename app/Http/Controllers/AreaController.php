<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Department;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('livewire.admin.areas.area-list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('livewire.admin.areas.area-create', compact('departments'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return view('livewire.admin.areas.area-show', compact('area'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        $departments = Department::orderBy('name')->get();
        return view('livewire.admin.areas.area-edit', compact('area', 'departments'));
    }
}
