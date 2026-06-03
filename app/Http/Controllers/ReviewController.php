<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Models\Book;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $validated =  $request->validated();
        
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);
        
        return redirect()->back();
    }

    public function edit(Review $review): View
    {
         $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        $review->delete();

        return redirect()->back();
    }
}
