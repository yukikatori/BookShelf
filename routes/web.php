<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;

// 書籍一覧・詳細
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->whereNumber('book')->name('books.show');

// ログイン必須
Route::middleware('auth')->group(function () {
    // 書籍お気に入り
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'favorites'])->name('favorites.toggle');

    // レビュー
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // レビューいいね
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like'])->name('reviews.like');

    // 書籍登録
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    // 書籍編集・削除
    Route::get('/book/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/book/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/book/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // ジャンルCRUD
    Route::resource('genres', GenreController::class);

    Route::get('/ranking', fn() => '準備中')->name('ranking.index');
});



