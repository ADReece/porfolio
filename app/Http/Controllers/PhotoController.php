<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:102400', // Max 100MB
            'set_id' => 'required|exists:sets,id'
        ]);

        $set = Set::findOrFail($request->input('set_id'));

        // Verify user owns this set
        if ($set->collection->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $uploadedFile = $request->file('file');
        $fileName = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

        // Store temporarily
        $temporaryPath = $uploadedFile->storeAs('temp', $fileName);

        // Dispatch job to process the image
        \App\Jobs\ProcessImageUpload::dispatch(
            auth()->user(),
            $temporaryPath,
            $set,
            $fileName
        );

        return response()->json([
            'success' => true,
            'fileName' => $fileName,
            'message' => 'File uploaded and queued for processing'
        ]);
    }

    public function update(Request $request, Photo $photo): JsonResponse
    {
        $this->authorize('update', $photo);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $photo->update($validated);

        return response()->json([
            'success' => true,
            'photo' => $photo,
            'message' => 'Photo updated successfully'
        ]);
    }

    public function destroy(Photo $photo): JsonResponse
    {
        $this->authorize('delete', $photo);

        // Delete from S3
        Storage::disk('s3')->delete($photo->url);
        Storage::disk('s3')->delete(str_replace('photos/', 'thumbnails/', $photo->url));
        Storage::disk('s3')->delete(str_replace('photos/', 'watermarked/', $photo->url));

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:photos,id',
        ]);

        $photos = Photo::whereIn('id', $validated['photo_ids'])
            ->where('user_id', auth()->id())
            ->get();

        foreach ($photos as $photo) {
            Storage::disk('s3')->delete($photo->url);
            Storage::disk('s3')->delete(str_replace('photos/', 'thumbnails/', $photo->url));
            Storage::disk('s3')->delete(str_replace('photos/', 'watermarked/', $photo->url));
            $photo->delete();
        }

        return response()->json([
            'success' => true,
            'message' => count($photos) . ' photos deleted successfully'
        ]);
    }

    public function move(Request $request, Photo $photo): JsonResponse
    {
        $this->authorize('update', $photo);

        $validated = $request->validate([
            'set_id' => 'required|exists:sets,id',
        ]);

        $set = Set::findOrFail($validated['set_id']);

        // Verify user owns the target set
        if ($set->collection->user_id !== auth()->id()) {
            abort(403);
        }

        $photo->update(['set_id' => $set->id]);

        return response()->json([
            'success' => true,
            'photo' => $photo,
            'message' => 'Photo moved successfully'
        ]);
    }

    public function requestDownload(Request $request, Photo $photo): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Generate a temporary download link (valid for 24 hours)
        $downloadUrl = Storage::disk('s3')->temporaryUrl(
            $photo->url,
            now()->addHours(24)
        );

        // Send email with download link
        try {
            \Mail::send('emails.photo-download', [
                'photo' => $photo,
                'downloadUrl' => $downloadUrl,
                'expiresAt' => now()->addHours(24)->format('F j, Y g:i A')
            ], function ($message) use ($validated, $photo) {
                $message->to($validated['email'])
                    ->subject('Your Requested Photo Download Link');
            });

            return response()->json([
                'success' => true,
                'message' => 'Download link sent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send download email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }

    public function requestPurchase(Request $request, Photo $photo): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        // Get photographer info
        $photographer = $photo->user;

        // Send email to client with purchase information
        try {
            \Mail::send('emails.photo-purchase-request', [
                'photo' => $photo,
                'photographer' => $photographer,
                'clientEmail' => $validated['email']
            ], function ($message) use ($validated, $photo) {
                $message->to($validated['email'])
                    ->subject('Photo Purchase Information');
            });

            // Optionally notify photographer
            \Mail::send('emails.purchase-notification', [
                'photo' => $photo,
                'clientEmail' => $validated['email']
            ], function ($message) use ($photographer, $photo) {
                $message->to($photographer->email)
                    ->subject('New Photo Purchase Request');
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase information sent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send purchase email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }
}
