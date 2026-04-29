<?php
use App\Http\Controllers\SqlRequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/submit', [SqlRequestController::class, 'index'])->name('submit.index');
    Route::post('/submit', [SqlRequestController::class, 'store'])->name('submit.store');
    

    Route::get('/approve', [ApprovalController::class, 'index'])->name('approve.index');
    Route::post('/approve/{id}', [ApprovalController::class, 'approve'])->name('approve.approve');
    Route::post('/reject/{id}', [ApprovalController::class, 'reject'])->name('approve.reject');

    Route::get('/my-requests', [SqlRequestController::class, 'myRequests'])->name('my-requests.index');
    
});

require __DIR__.'/auth.php';
