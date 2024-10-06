<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/upload', [PhotoController::class, 'upload'])->name('upload.view');
    Route::post('/upload', [PhotoController::class, 'upload'])->name('upload.create');
    Route::patch('/upload', [PhotoController::class, 'upload'])->name('upload.update');
    Route::delete('/upload', [PhotoController::class, 'upload'])->name('upload.destroy');

});

Route::prefix('/@{username}')->group(function(){
    Route::get('/', [ProfileController::class, 'view'])->name('profile.view');

    Route::get('/albums', [CollectionController::class, 'index'])->name('albums.index');
    Route::get('/albums/{album_id}', [CollectionController::class, 'show'])->name('albums.show');

    Route::get('/media/{media_id}', [PhotoController::class, 'show'])->name('media.show');
});

require __DIR__.'/auth.php';
