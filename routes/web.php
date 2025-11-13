<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SetController;
use App\Http\Livewire\ManageSets;
use App\Http\Livewire\PhotoUpload;
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


//Backend Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/display', [ProfileController::class, 'updateDisplayMode'])->name('profile.update-display');
    Route::patch('/profile/watermark', [ProfileController::class, 'updateWatermark'])->name('profile.update-watermark');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/upload-files', [PhotoController::class, 'upload'])->name('upload-files');

    Route::group(['prefix' => 'collections'], function () {
        Route::get('/', [CollectionController::class, 'index'])->name('collections.index');
        Route::get('/create', [CollectionController::class, 'create'])->name('collections.create');
        Route::post('/create', [CollectionController::class, 'store'])->name('collections.store');
        Route::get('/{collection}/edit', [CollectionController::class, 'edit'])->name('collections.edit');
        Route::patch('/{collection}', [CollectionController::class, 'update'])->name('collections.update');
        Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');

        Route::get('/{collection}/sets', [CollectionController::class, 'sets'])->name('sets.create');
    });

    // Sets routes
    Route::get('/sets/{set}', [SetController::class, 'show'])->name('sets.detail');
    Route::post('/collections/{collection}/sets', [SetController::class, 'store'])->name('sets.store');
    Route::patch('/collections/{collection}/sets/{set}', [SetController::class, 'update'])->name('sets.update');
    Route::delete('/collections/{collection}/sets/{set}', [SetController::class, 'destroy'])->name('sets.destroy');

    Route::post('/collections/{collection}/email-client', [CollectionController::class, 'emailClient'])->name('collections.email-client');
    Route::post('/collections/{collection}/request-archive', [CollectionController::class, 'requestArchive'])->name('collections.request-archive');
    Route::get('/collections/{collection}/archive/{filename}', [CollectionController::class, 'archiveDownloadPage'])->name('collections.archive-download-page');
    Route::get('/collections/{collection}/download-archive/{filename}', [CollectionController::class, 'downloadArchive'])->name('collections.download-archive');


    // Photo management
    Route::group(['prefix' => 'photos'], function () {
        Route::patch('/{photo}', [PhotoController::class, 'update'])->name('photos.update');
        Route::delete('/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
        Route::post('/bulk-delete', [PhotoController::class, 'bulkDelete'])->name('photos.bulk-delete');
        Route::post('/{photo}/move', [PhotoController::class, 'move'])->name('photos.move');
    });
});



//Public facing Routes
Route::prefix('/@{username}')->group(function(){
    Route::get('/', [ProfileController::class, 'view'])->name('profile.view');

    Route::get('/collections', [ProfileController::class, 'collections'])->name('profile.collections');
    Route::get('/collections/{collection_id}', [ProfileController::class, 'collection'])->name('profile.collection');
});

// Public photo download and purchase requests
Route::post('/photos/{photo}/request-download', [PhotoController::class, 'requestDownload'])->name('photos.request-download');
Route::post('/photos/{photo}/request-purchase', [PhotoController::class, 'requestPurchase'])->name('photos.request-purchase');

require __DIR__.'/auth.php';
