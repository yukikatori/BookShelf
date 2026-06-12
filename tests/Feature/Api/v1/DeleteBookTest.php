<?php

namespace Tests\Feature\Api\v1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class DeleteBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍を削除することができ、２０４NoContentが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $response = $this->deleteJson('/api/v1/books/' . $book->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        foreach ($genres as $genre) {
            $this->assertDatabaseMissing('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }
}
