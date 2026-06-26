<?php

namespace Tests\Feature\Api\v1\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
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

        $book->genres()->attach($genres->pluck('id'));

        Sanctum::actingAs($user);
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

    /** @test */
    public function 未認証時は書籍が削除できず401が返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $response = $this->deleteJson('/api/v1/books/' . $book->id);

        $response->assertStatus(401);
    }

    /** @test */
    public function 未認可時は書籍が削除できず403が返る(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user1->id]);
        $genres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        Sanctum::actingAs($user2);
        $response = $this->deleteJson('/api/v1/books/' . $book->id);

        $response->assertStatus(403);
    }
}
