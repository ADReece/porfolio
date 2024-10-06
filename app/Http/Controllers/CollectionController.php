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



    public function create() : View
    {
        return view('collections.backend.create');
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
