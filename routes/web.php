<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::controller(RegisteredUserController::class)->group(function () {
    Route::get('/register', 'create')->name('register');
    Route::post('/register', 'store')->name('register');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/', [BookController::class, 'index'])->name('home');

Route::controller(BookController::class)->group(function () {
    Route::prefix('books')->middleware('auth')->group(function () {
        Route::get('create', 'create')->name('books.create');
        Route::post('', 'store')->name('books.store');
        Route::get('{book}/edit', 'edit')->name('books.edit');
        Route::put('{book}', 'update')->name('books.update');
        Route::delete('{book}', 'destroy')->name('books.destroy');
    });

    Route::prefix('books')->group(function () {
        Route::get('', 'index')->name('books.index');
        Route::get('{book}', 'show')->name('books.show');
    });
});

Route::get('/ranking', function () {
    return 'ランキング画面';
})->name('ranking.index'); // 仮ルート

Route::get('/favorites', function () {
    return 'お気に入り一覧画面';
})->name('favorites.index'); // 仮ルート

Route::get('/genres', function () {
    return 'ジャンル一覧画面';
})->name('genres.index'); // 仮ルート
