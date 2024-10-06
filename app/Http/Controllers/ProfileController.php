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
        $collection = $user->collections->find($collection_id)->with(['sets.photos'])->firstOrFail();

        if(is_null($collection)){
            abort(404);
        }
        if($collection->private){
            $password = $request->get('password');
            if(is_null($password)){
                return view('collections.frontend.password', ['collection' => $collection]);
            }
            if(!password_verify($password, $collection->password)){
                return view('collections.frontend.password', ['collection' => $collection])->withErrors(['password' => 'Invalid Password']);
            }
        }

        return view('collections.frontend.show', ['collection' => $collection]);
    }

}
