<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function index()
    {
        return view('livewire.admin.holidays.holiday-list');
    }

    public function create()
    {
        return view('livewire.admin.holidays.holiday-create');
    }

    public function show(Holiday $holiday)
    {
        return view('livewire.admin.holidays.holiday-show', compact('holiday'));
    }

    public function edit(Holiday $holiday)
    {
        return view('livewire.admin.holidays.holiday-edit', compact('holiday'));
    }
}
