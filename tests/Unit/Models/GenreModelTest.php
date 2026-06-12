<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;

class GenreModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 中間テーブルを介して、１つのジャンルが複数の書籍に紐づいている(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(3)->create();

        $genre->books()->attach($books->pluck('id'));

        foreach ($books as $book) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id
            ]);
        }

        $this->assertCount(3, $genre->books);
        $this->assertInstanceOf(Book::class, $genre->books->first());
    }
}
