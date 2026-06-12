<?php

namespace Tests\Feature\Api\v1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class UpdateBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍が編集後、ジャンルが紐づけられjsonが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertDatabaseHas('books', [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => null,
            'user_id' => $user->id,
        ]);

        $book = Book::first();

        foreach ($newGenres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    /** @test */
    public function タイトルが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => '',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function タイトルが文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 123,
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function タイトルが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => str_repeat('a', 256),
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function 著者が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => '',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function 著者が文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 123,
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function 著者が255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => str_repeat('a', 256),
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function ISBNが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function ISBNが13桁以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function ISBNコードが既存のISBNコードと重複するとバリデーションエラーが返る(): void
    {
        $book = Book::factory()->create(['isbn' => '1111111111111']);

        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function ISBNコードが既存のISBNコードと重複するとバリデーションエラーが返る（自身は除く）(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);

        $this->assertDatabaseHas('books', [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => null,
            'user_id' => $user->id,
        ]);

        $book = Book::first();

        foreach ($newGenres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    /** @test */
    public function 出版日が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('published_date');
    }

    /** @test */
    public function 出版日が日付形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '123',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('published_date');
    }

    /** @test */
    public function 画像URLが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg' . str_repeat('a', 256),
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image_url');
    }

    /** @test */
    public function 画像URLがURL形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '123',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image_url');
    }

    /** @test */
    public function 説明文が1000文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => str_repeat('a', 1001),
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function 説明文が文字列以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 1223,
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function ジャンルが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => '',
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres');
    }


    /** @test */
    public function 存在しないジャンルを指定するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => ['fail'],
            'user_id' => $user->id,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function ユーザーが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => '',
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function 存在しないユーザーを指定するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book->genres()->attach($genres->pluck('id'));

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $newGenres->pluck('name')->toArray(),
            'user_id' => 999,
        ];

        $response = $this->putJson('/api/v1/books/' . $book->id, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }
}
