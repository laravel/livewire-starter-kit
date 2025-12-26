<?php

namespace App\Http\Controllers;

use App\Models\SentList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SentListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sentLists = SentList::with(['purchaseOrder.part', 'workOrders', 'shifts'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('sent-lists.index', compact('sentLists'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SentList $sentList)
    {
        $sentList->load(['purchaseOrder.part', 'workOrders.purchaseOrder.part', 'shifts']);

        return view('sent-lists.show', compact('sentList'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SentList $sentList)
    {
        // Only pending sent lists can be edited
        if (!$sentList->isPending()) {
            return redirect()->route('sent-lists.show', $sentList)
                ->with('error', 'Only pending sent lists can be edited.');
        }

        return view('sent-lists.edit', compact('sentList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SentList $sentList)
    {
        // Only pending sent lists can be updated
        if (!$sentList->isPending()) {
            return redirect()->route('sent-lists.show', $sentList)
                ->with('error', 'Only pending sent lists can be updated.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,canceled',
        ]);

        $sentList->update($validated);

        return redirect()->route('sent-lists.show', $sentList)
            ->with('success', 'Sent list updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SentList $sentList)
    {
        if (!$sentList->canBeDeleted()) {
            return redirect()->route('sent-lists.index')
                ->with('error', 'Cannot delete confirmed sent lists.');
        }

        $sentList->delete();

        return redirect()->route('sent-lists.index')
            ->with('success', 'Sent list deleted successfully.');
    }
}
