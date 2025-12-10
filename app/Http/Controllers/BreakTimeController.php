<?php

namespace App\Http\Controllers;

use App\Models\BreakTime;
use App\Http\Requests\StoreBreakTimeRequest;
use App\Http\Requests\UpdateBreakTimeRequest;

class BreakTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('breaktimes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('breaktimes.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(BreakTime $breakTime)
    {
        return view('breaktimes.show', compact('breakTime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BreakTime $breakTime)
    {
        return view('breaktimes.edit', compact('breakTime'));
    }
}
