<?php

namespace App\Http\Controllers;

use App\Models\Set;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SetController extends Controller
{
    public function store(Request $request, Collection $collection): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $set = $collection->sets()->create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'set' => $set,
            'message' => 'Set created successfully'
        ]);
    }

    public function update(Request $request, Set $set): JsonResponse
    {
        $this->authorize('update', $set);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $set->update($validated);

        return response()->json([
            'success' => true,
            'set' => $set,
            'message' => 'Set updated successfully'
        ]);
    }

    public function destroy(Set $set): JsonResponse
    {
        $this->authorize('delete', $set);

        $set->delete();

        return response()->json([
            'success' => true,
            'message' => 'Set deleted successfully'
        ]);
    }

    public function show(Set $set): View
    {
        $this->authorize('view', $set);

        return view('collections.backend.set-detail', [
            'set' => $set->load('photos', 'collection')
        ]);
    }
}

