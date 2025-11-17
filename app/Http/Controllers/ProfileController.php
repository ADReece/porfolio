<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's portfolio display mode.
     */
    public function updateDisplayMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'portfolio_display_mode' => 'required|in:grid,collections',
            'masonry_columns' => 'required|integer|min:2|max:6',
            'photos_per_page' => 'required|integer|min:10|max:100',
            'portfolio_font' => 'nullable|string|in:system,nunito,inter,playfair,roboto,open-sans',
            'portfolio_accent_color' => 'nullable|regex:/^#([A-Fa-f0-9]{6})$/',
            'portfolio_theme' => 'nullable|in:auto,light,dark',
        ]);

        $request->user()->update([
            'portfolio_display_mode' => $validated['portfolio_display_mode'],
            'masonry_columns' => $validated['masonry_columns'],
            'photos_per_page' => $validated['photos_per_page'],
            'portfolio_font' => $validated['portfolio_font'] ?? $request->user()->portfolio_font,
            'portfolio_accent_color' => $validated['portfolio_accent_color'] ?? $request->user()->portfolio_accent_color,
            'portfolio_theme' => $validated['portfolio_theme'] ?? $request->user()->portfolio_theme,
        ]);

        return Redirect::route('profile.edit')->with('status', 'display-updated');
    }

    /**
     * Update the user's watermark settings.
     */
    public function updateWatermark(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'watermark_text' => 'nullable|string|max:50',
        ]);

        $request->user()->update([
            'watermark_text' => $validated['watermark_text']
        ]);

        return Redirect::route('profile.edit')->with('status', 'watermark-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * View the users account.
     */
    public function view(Request $request, $username): View
    {
        $user = User::where('username', $username)->first();

        if(!is_null($user)){
            // Determine which view to show based on user's preference
            if ($user->portfolio_display_mode === 'collections') {
                // Load published, non-hidden, non-private collections with cover photos
                $collections = $user->collections()
                    ->where('status', 'Published')
                    ->where('hide_from_portfolio', false)
                    ->where('private', false) // Exclude private collections from public portfolio
                    ->with('coverPhoto')
                    ->orderBy('event_date', 'desc')
                    ->get();

                return view('profile.view-collections', [
                    'user' => $user,
                    'collections' => $collections
                ]);
            }

            // Default to grid view
            return view('profile.view', ['user' => $user]);
        }

        return abort(404);

    }

    public function collections($username) : View
    {
        $collections = User::where('username', $username)->firstOrFail()?->collections;

        return view('collections.frontend.index', ['collections' => $collections]);
    }

    public function collection(Request $request, $username, $collection_id) : View
    {
        $user = User::where('username', $username)->firstOrFail();

        // Load collection with sets and filter photos based on privacy
        $collection = $user->collections()
            ->where('id', $collection_id)
            ->firstOrFail();

        if(is_null($collection)){
            abort(404);
        }

        // Handle password protection for private collections
        if($collection->private){
            $password = $request->get('password');
            if(is_null($password)){
                return view('collections.frontend.password', ['collection' => $collection]);
            }
            if(!password_verify($password, $collection->password)){
                return view('collections.frontend.password', ['collection' => $collection])->withErrors(['password' => 'Invalid Password']);
            }
        }

        // Load sets with photos - only show non-private photos unless it's a private collection
        $collection->load(['sets' => function($query) use ($collection) {
            $query->with(['photos' => function($q) use ($collection) {
                // If collection is private and user verified, show all photos
                // Otherwise only show public photos (private = 0)
                if (!$collection->private) {
                    $q->where('private', false);
                }
            }]);
        }]);

        return view('collections.frontend.show', ['collection' => $collection]);
    }

}
