<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\BookIsbnController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// ゲスト
Route::middleware('guest')->group(function () {
    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('/register', 'create')->name('register');
        Route::post('/register', 'store');
    });

    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store');
    });
});

// 認証必要
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::post('/books/{book}/favorites', [FavoriteController::class, 'store'])->name('favorites.toggle');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::controller(BookController::class)->prefix('books')->group(function () {
        Route::get('create', 'create')->name('books.create');
        Route::get('isbn/{isbn}', BookIsbnController::class)->name('books.isbn');
        Route::post('', 'store')->name('books.store');
        Route::get('{book}/edit', 'edit')->name('books.edit');
        Route::put('{book}', 'update')->name('books.update');
        Route::delete('{book}', 'destroy')->name('books.destroy');
    });

    Route::post('/reviews/{review}/like', [LikeController::class, 'store'])->name('reviews.like');

    Route::controller(ReviewController::class)->group(function () {
        Route::post('/books/{book}/reviews', 'store')->name('reviews.store');
        Route::get('/reviews/{review}/edit', 'edit')->name('reviews.edit');
        Route::put('/reviews/{review}', 'update')->name('reviews.update');
        Route::delete('/reviews/{review}', 'destroy')->name('reviews.destroy');
    });

    Route::controller(GenreController::class)->prefix('genres')->group(function () {
        Route::get('', 'index')->name('genres.index');
        Route::get('create', 'create')->name('genres.create');
        Route::post('', 'store')->name('genres.store');
        Route::get('{genre}', 'show')->name('genres.show');
        Route::get('{genre}/edit', 'edit')->name('genres.edit');
        Route::put('{genre}', 'update')->name('genres.update');
        Route::delete('{genre}', 'destroy')->name('genres.destroy');
    });

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// 公開
Route::controller(BookController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/books', 'index')->name('books.index');
    Route::get('/books/{book}', 'show')->name('books.show');
});

Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// 応用編仮ルート
Route::view('/reading-plans', 'reading-plans.index')->name('reading-plans.index');
Route::view('/notifications', 'notifications.index')->name('notifications.index');
