<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function favorites(Book $book): RedirectResponse
    {
        $user = auth()->user();
        $user->favoriteBooks()->toggle($book->id);

        return redirect()->route('books.show', $book);
    }

    public function index(): View
    {
        $user = auth()->user();
        $books = $user->favoriteBooks()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
