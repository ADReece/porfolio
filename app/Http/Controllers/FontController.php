<?php

namespace App\Http\Controllers;

use App\Models\Font;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FontController extends Controller
{
    public function index(): View
    {
        $systemFonts = Font::whereNull('user_id')->where('active', true)->get();
        $userFonts   = auth()->user()->fonts()->where('active', true)->latest()->get();

        return view('fonts.index', compact('systemFonts', 'userFonts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'font' => [
                'required',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['ttf', 'otf'])) {
                        $fail('The font must be a TTF or OTF file.');
                    }
                },
            ],
        ]);

        $file     = $request->file('font');
        $filename = $file->getClientOriginalName();
        $path     = $file->store('fonts/' . auth()->id(), 's3');

        auth()->user()->fonts()->create([
            'name'     => $request->input('name'),
            'filename' => $filename,
            'path'     => $path,
        ]);

        return back()->with('success', 'Font uploaded successfully.');
    }

    public function destroy(Font $font)
    {
        if ($font->user_id !== auth()->id()) {
            abort(403);
        }

        Storage::disk('s3')->delete($font->path);
        $font->delete();

        return back()->with('success', 'Font deleted.');
    }
}
