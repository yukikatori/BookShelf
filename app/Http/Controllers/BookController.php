<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function index(IndexBookRequest $request): View
    {
        $validated = $request->validated();

        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->filter($validated)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($validated);

        $genres = Genre::all();
        
        return view('books.index', compact('books', 'genres'));
    }

    public function show(Book $book): View
    {
        $book->load('genres', 'reviews');

        return view('books.show', compact('book'));
    }

    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function searchByIsbn($isbn): JsonResponse
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn,
        ]);

        $data = $response->json();

        if (!isset($data['items'][0])) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。',
            ], 404);
        }

        $book = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $book['title'] ?? null,
            'author' => $book['authors'][0] ?? null,
            'published_date' => $book['publishedDate'] ?? null,
            'image_url' => $book['imageLinks']['thumbnail'] ?? null,
        ]);
    }


    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $book = Book::create([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました');
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        $book->delete();

        return redirect()
        ->route('books.index')
        ->with('success', '書籍を削除しました');
    }
}
