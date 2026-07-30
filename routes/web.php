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

Route::controller(BookController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/books', 'index')->name('books.index');
    Route::get('/books/{book}', 'show')->name('books.show');
});

Route::get('/books/create', function () {
    return '仮の書籍作成画面';
})->name('books.create'); // 仮ルート

Route::get('/ranking', function () {
    return 'ランキング画面';
})->name('ranking.index'); // 仮ルート

Route::get('/favorites', function () {
    return 'お気に入り一覧画面';
})->name('favorites.index'); // 仮ルート

Route::get('/genres', function () {
    return 'ジャンル一覧画面';
})->name('genres.index'); // 仮ルート
