<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function favorite(Book $book): RedirectResponse
    {
        $user = auth()->user();
        $user->favoriteBooks()->toggle($book->id);

        return redirect()->back();
    }
}
