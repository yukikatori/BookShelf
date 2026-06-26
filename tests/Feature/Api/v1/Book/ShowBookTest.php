<?php

namespace Tests\Feature\Api\v1\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class ShowBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function json形式の詳細が返る(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this->getJson('/api/v1/books/' . $book->id);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);

        $this->assertEquals($book->id, $json['data']['id']);
    }

    /** @test */
    public function 書籍詳細が存在しない場合、バリデーションエラーが返る(): void
    {
        $response = $this->getJson('/api/v1/books/' . 999);

        $response->assertStatus(404);
    }
}
