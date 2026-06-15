<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // 基本統計
        $totalReviews = Review::where('user_id', $user->id)->count();

        // ↓暫定
        $booksRead = Review::where('user_id', $user->id)
            ->distinct('book_id')
            ->count();

        $averageRating = Review::where('user_id', $user->id)
            ->avg('rating');

        // 評価分布
        $reviews = Review::where('user_id', $user->id)
            ->select('rating')
            ->get()
            ->groupBy('rating');

        $ratingDistribution = collect(range(1, 5))
            ->map(fn($rating) => $reviews->get($rating, collect())->count());

        // 高評価書籍TOP5
        $books = Book::with(['reviews' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])->get();

        $topRatedBooks = $books
            ->map(function ($book) {
                $userReviews = $book->reviews;

                $highReviews = $userReviews->where('rating', '>=', 4);

                $maxRating = $highReviews->max('rating');

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'rating' => $maxRating,
                ];
            })
            ->filter(fn($b) => $b['rating'] > 0)
            ->sortByDesc('rating')
            ->take(5)
            ->values();
        
        // ジャンル別評価傾向
        $genres = Genre::with(['books.reviews' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])->get();

        $genreRatings = $genres
            ->map(function ($genre) {
                $userReviews = $genre->books
                    ->flatMap(fn($book) => $book->reviews);

                $count = $userReviews->count();

                $avg = $userReviews->avg('rating');

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => $count,
                    'average_rating' => $avg,
                ];
            })
            ->filter(fn($g) => $g['count'] > 0)
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        // データまとめ
        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
