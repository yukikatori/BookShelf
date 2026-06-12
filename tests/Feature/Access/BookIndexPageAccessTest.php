<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;

class BookIndexPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧が１０件／ページでページネーション表示され、各書籍にジャンルが紐づく(): void
    {
        $books = Book::factory()->count(15)->create();
        $genres = Genre::factory()->count(5)->create();

        foreach ($books as $book) {
            $selectedGenres = $genres->random(rand(2,3))->pluck('id');
        
            $book->genres()->attach($selectedGenres);
        }

        $response = $this->get('/');

        $response->assertViewHas('books', function ($viewBooks) {
            return $viewBooks->count() === 10;
        });

        $response->assertSee('page=2');

        $response->assertViewHas('books', function ($viewBooks) {
            return $viewBooks->every(fn ($book) => $book->genres->isNotEmpty());
        });
    }

    /** @test */
    public function ゲストが書籍一覧にアクセスできる(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
