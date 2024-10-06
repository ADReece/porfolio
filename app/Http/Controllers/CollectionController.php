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
        $albums = auth()->user()->albums->public();

        return view('albums.index', ['albums' => $albums]);
    }

    public function show(Request $request, $username, $album_id) : View
    {
        $user = User::where('username', $username)->firstOrFail();
        $album = $user->albums->find($album_id)->with(['media'])->firstOrFail();

        if(is_null($album)){
            abort(404);
        }
        if(!$album->public){
            $password = $request->get('password');
            if(is_null($password)){
                return view('albums.password', ['album' => $album]);
            }
            if(!password_verify($password, $album->password)){
                return view('albums.password', ['album' => $album])->withErrors(['password' => 'Invalid Password']);
            }
        }

        return view('albums.show', ['album' => $album]);
    }

    public function create() : View
    {
        return view('albums.create');
    }

    public function store(Request $request) : JsonResponse
    {

    }

    public function update(Request $request) : JsonResponse
    {

    }

    public function delete(Request $request) : JsonResponse
    {

    }
}
