<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index(): View
    {
        $genres = Genre::withCount('books')
            ->orderBy('id')
            ->get();

        return view('genres.index', compact('genres'));
    }

    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function create(): View
    {
        return view('genres.create');
    }

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'このジャンルには書籍が紐づいているので削除できません');
        }

        $genre->delete();

        return redirect()
            ->back()
            ->with('success', 'ジャンルを削除しました');
    }
}
