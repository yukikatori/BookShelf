<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReportController;

// 書籍一覧・詳細
Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->whereNumber('book')->name('books.show');

// ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ログインが必要な項目
Route::middleware('auth')->group(function () {
    // 書籍CRUD
    Route::resource('books', BookController::class)->except('index', 'show');
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])->name('books.searchByIsbn');

    // 書籍お気に入り
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'favorites'])->name('favorites.toggle');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    
    // レビュー
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // レビューいいね
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like'])->name('reviews.like');

    // ジャンルCRUD
    Route::resource('genres', GenreController::class);

    // マイ読書レポート
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // 読書計画
    Route::resource('reading-plans', ReadingPlanController::class);
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
});
