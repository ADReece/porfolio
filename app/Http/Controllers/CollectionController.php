<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionController extends Controller
{

    public function index() : View
    {
        $collections = auth()->user()->collections;
        return view('collections.backend.index', ['collections' => $collections]);
    }

    public function sets($collection) : View
    {
        return view('collections.backend.sets', ['collection' => $collection]);
    }



    public function create() : View
    {
        return view('collections.backend.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'nullable|date',
            'status' => 'nullable|string|in:Draft,Published,Archived',
            'private' => 'nullable|boolean',
            'password' => 'nullable|string|min:4',
        ]);

        $collection = auth()->user()->collections()->create([
            'name' => $validated['name'],
            'event_date' => $validated['event_date'] ?? null,
            'status' => $validated['status'] ?? 'Draft',
            'private' => $validated['private'] ?? false,
            'password' => $validated['password'] ? bcrypt($validated['password']) : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection created successfully'
            ]);
        }

        return redirect()->route('collections.edit', $collection)
            ->with('success', 'Collection created successfully');
    }

    public function edit($collectionId) : View
    {
        $collection = auth()->user()->collections()->findOrFail($collectionId);
        return view('collections.backend.edit', ['collection' => $collection]);
    }

    public function update(Request $request, $collectionId)
    {
        $collection = auth()->user()->collections()->findOrFail($collectionId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_date' => 'nullable|date',
            'status' => 'nullable|string|in:Draft,Published,Archived',
            'private' => 'nullable|boolean',
            'password' => 'nullable|string|min:4',
            'cover_photo_id' => 'nullable|exists:photos,id',
            'watermarked' => 'nullable|boolean',
            'hide_from_portfolio' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'event_date' => $validated['event_date'] ?? null,
            'status' => $validated['status'] ?? $collection->status,
            'private' => $request->has('private') ? true : false,
            'cover_photo_id' => $validated['cover_photo_id'] ?? $collection->cover_photo_id,
            'watermarked' => $request->has('watermarked') ? true : false,
            'hide_from_portfolio' => $request->has('hide_from_portfolio') ? true : false,
        ];

        if (isset($validated['password']) && $validated['password']) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $collection->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection updated successfully'
            ]);
        }

        return back()->with('success', 'Collection updated successfully');
    }

    public function destroy($collectionId)
    {
        $collection = auth()->user()->collections()->findOrFail($collectionId);
        $collection->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Collection deleted successfully'
            ]);
        }

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully');
    }

    public function emailClient(Request $request, $collectionId)
    {
        $collection = auth()->user()->collections()->findOrFail($collectionId);

        // Verify collection is private and published
        if (!$collection->private || $collection->status !== 'Published') {
            return response()->json([
                'success' => false,
                'message' => 'This collection cannot be shared via email'
            ], 400);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            // Get the plain text password from session or generate a shareable token
            // Note: We cannot retrieve the hashed password, so we'll need to handle this differently
            // For now, we'll send the link and ask them to contact you for the password
            // In production, you might want to generate a temporary access token instead

            $collectionUrl = route('profile.collection', [
                'username' => auth()->user()->username,
                'collection_id' => $collection->id
            ]);

            \Mail::send('emails.collection-share', [
                'collection' => $collection,
                'collectionUrl' => $collectionUrl,
                'clientName' => $validated['name'] ?? 'there',
                'customMessage' => $validated['message'] ?? null,
                'photographerName' => auth()->user()->name ?? auth()->user()->username,
            ], function ($message) use ($validated, $collection) {
                $message->to($validated['email'])
                    ->subject('Your Private Gallery: ' . $collection->name);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send collection email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
