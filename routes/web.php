<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


use App\Http\Controllers\MysteryCaseController;
use App\Http\Controllers\ClueController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\SceneController;

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'admin'])->get('/admin/test', function () {
    return response()->json([
        'message' => 'Welcome Admin',
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/mystery-cases', [
        MysteryCaseController::class,
        'index',
    ]);
    Route::get('/mystery-cases/{mysteryCase}', [
        MysteryCaseController::class,
        'show',
    ]);
    Route::put('/mystery-cases/{mysteryCase}', [
        MysteryCaseController::class,
        'update',
    ]);
    Route::delete('/mystery-cases/{mysteryCase}', [
        MysteryCaseController::class,
        'destroy',
    ]);
    Route::post('/mystery-cases/{mysteryCase}/publish', [
        MysteryCaseController::class,
        'publish',
    ]);
    Route::post('/mystery-cases/{mysteryCase}/archive', [
        MysteryCaseController::class,
        'archive',
    ]);

    Route::post('/mystery-cases/{mysteryCase}/clues', [
        ClueController::class,
        'store',
    ]);
    Route::get('/mystery-cases/{mysteryCase}/clues', [
        ClueController::class,
        'index',
    ]);
    Route::get('/clues/{clue}', [
        ClueController::class,
        'show',
    ]);
    Route::put('/clues/{clue}', [
        ClueController::class,
        'update',
    ]);
    Route::delete('/clues/{clue}', [
        ClueController::class,
        'destroy',
    ]);
    Route::post('/clues/{clue}/reveal', [
        ClueController::class,
        'reveal',
    ]);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/mystery-cases', [MysteryCaseController::class,'store']);
    Route::get('/mystery-cases', [MysteryCaseController::class,'index']);
    Route::get('/mystery-case/{mysteryCase}', [MysteryCaseController::class,'show']);
    Route::put('/mystery-cases/{mysteryCase}', [MysteryCaseController::class,'update']);
    Route::delete('/mystery-cases/{mysteryCase}', [MysteryCaseController::class,'destroy']);
    Route::post('/mystery-cases/{mysteryCase}/publish', [MysteryCaseController::class,'publish']);
    Route::post('/mystery-cases/{mysteryCase}/archive', [MysteryCaseController::class,'archive']);
});


Route::get('/mystery-cases/{mysteryCase}/evidence', [EvidenceController::class,'index'])
    ->middleware(['auth', 'admin']);
Route::post('/mystery-cases/{mysteryCase}/evidence', [EvidenceController::class, 'store'])
    ->middleware(['auth', 'admin']);
Route::get('/mystery-cases/{mysteryCase}/evidence/{evidence}', [EvidenceController::class,'show']) 
    ->middleware(['auth', 'admin']);
Route::put('/mystery-cases/{mysteryCase}/evidence/{evidence}', [EvidenceController::class,'update'])
    ->middleware(['auth', 'admin']);
Route::delete('/mystery-cases/{mysteryCase}/evidence/{evidence}', [EvidenceController::class,'destroy'])
    ->middleware(['auth', 'admin']);






  
Route::post('/mystery-cases/{mysteryCase}/chapters',[ChapterController::class, 'store'])
    ->middleware(['auth', 'admin']);
Route::get('/mystery-cases/{mysteryCase}/chapters/{chapter}', [ChapterController::class, 'show'])
    ->middleware(['auth', 'admin']);
Route::put('/mystery-cases/{mysteryCase}/chapters/{chapter}', [ChapterController::class, 'update'])
    ->middleware(['auth', 'admin']);
Route::delete('/mystery-cases/{mysteryCase}/chapters/{chapter}', [ChapterController::class, 'destroy'])
    ->middleware(['auth', 'admin']);


Route::post('/chapters/{chapter}/scenes',[SceneController::class, 'store'])
    ->middleware(['auth', 'admin']);
Route::get('/chapters/{chapter}/scenes/{scene}', [SceneController::class, 'show'])
    ->middleware(['auth', 'admin']);
Route::put('/chapters/{chapter}/scenes/{scene}', [SceneController::class, 'update'])
    ->middleware(['auth', 'admin']);
Route::delete('/chapters/{chapter}/scenes/{scene}', [SceneController::class, 'destroy'])
    ->middleware(['auth', 'admin']);


Route::get('/scenes/{scene}/interactables', [App\Http\Controllers\InteractableController::class, 'index'])
    ->middleware(['auth', 'admin']);
Route::post('/scenes/{scene}/interactables', [App\Http\Controllers\InteractableController::class, 'store'])
    ->middleware(['auth', 'admin']);
Route::get('/scenes/{scene}/interactables/{interactable}', [App\Http\Controllers\InteractableController::class, 'show'])
    ->middleware(['auth', 'admin']);
Route::put('/scenes/{scene}/interactables/{interactable}', [App\Http\Controllers\InteractableController::class, 'update'])
    ->middleware(['auth', 'admin']);
Route::delete('/scenes/{scene}/interactables/{interactable}', [App\Http\Controllers\InteractableController::class, 'destroy'])
    ->middleware(['auth', 'admin']);

require __DIR__.'/auth.php';
