<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;

// 書籍一覧・詳細
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');


// ログイン必須
Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');

    // 書籍お気に入り
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'favorite'])->name('favorites.toggle');

    // レビュー
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like'])->name('reviews.like');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');


    Route::post('/', fn() => '準備中')->name('books.store');
    Route::get('/ranking', fn() => '準備中')->name('ranking.index');
    Route::get('/favorites', fn() => '準備中')->name('favorites.index');
    Route::get('/genres', fn() => '準備中')->name('genres.index');
});

