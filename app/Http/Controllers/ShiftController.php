<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        return view('shifts.index');
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function show(Shift $shift)
    {
        return view('shifts.show', compact('shift'));
    }

    public function edit(Shift $shift)
    {
        return view('shifts.edit', compact('shift'));
    }
}
