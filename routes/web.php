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


//Backend Routes
Route::middleware('auth')->group(function () {
    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::group(['prefix' => 'collections'], function () {
        Route::get('/', [CollectionController::class, 'index'])->name('collections.index');
        Route::get('/create', [CollectionController::class, 'create'])->name('collections.create');
        Route::post('/create', [CollectionController::class, 'store'])->name('collections.store');
        Route::get('/{collection}/edit', [CollectionController::class, 'edit'])->name('collections.edit');
        Route::patch('/{collection}', [CollectionController::class, 'update'])->name('collections.update');
        Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');
    });
});



//Public facing Routes
Route::prefix('/@{username}')->group(function(){
    Route::get('/', [ProfileController::class, 'view'])->name('profile.view');

    Route::get('/collections', [ProfileController::class, 'collections'])->name('profile.collections');
    Route::get('/collections/{collection_id}', [ProfileController::class, 'collection'])->name('profile.collection');

});

require __DIR__.'/auth.php';
