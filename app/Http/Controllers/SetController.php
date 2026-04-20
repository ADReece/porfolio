<?php

namespace App\Http\Controllers;

use App\Models\Set;
use App\Models\Collection;
use App\Models\Template;
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

        $userTemplates = Template::where('user_id', auth()->id())
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('collections.backend.set-detail', [
            'set'           => $set->load('photos', 'collection', 'templates'),
            'userTemplates' => $userTemplates,
        ]);
    }

    public function syncTemplates(Request $request, Set $set): JsonResponse
    {
        $this->authorize('update', $set);

        $validated = $request->validate([
            'template_ids'   => 'present|array',
            'template_ids.*' => 'uuid|exists:templates,id',
        ]);

        // Only allow attaching templates owned by the current user
        $allowed = Template::where('user_id', auth()->id())
            ->whereIn('id', $validated['template_ids'])
            ->pluck('id')
            ->all();

        $set->templates()->sync($allowed);

        return response()->json([
            'success' => true,
            'message' => 'Templates updated.',
        ]);
    }
}

