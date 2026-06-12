<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Review;

class ReviewLikeController extends Controller
{
    public function like(Review $review): RedirectResponse
    {
        $user = auth()->user();
        $user->likedReviews()->toggle($review->id);

        return redirect()->route('books.show', $review->book);
    }
}
