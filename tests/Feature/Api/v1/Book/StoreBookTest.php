<?php

namespace Tests\Feature\Api\v1\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class StoreBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍が登録後、ジャンルが紐づけられjsonが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-13',
            'description' => 'test',
            'image_url' => '',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(201);

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

        foreach ($genres as $genre) {
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
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => '',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function タイトルが文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 123,
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function タイトルが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => str_repeat('a', 256),
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function 著者が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => '',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function 著者が文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 123,
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function 著者が255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => str_repeat('a', 256),
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('author');
    }

    /** @test */
    public function ISBNが13桁以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '11111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function ISBNコードが既存のISBNコードと重複するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'isbn' => '1111111111111',
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function 出版日が日付形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => 'test',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('published_date');
    }

    /** @test */
    public function 画像URLが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg' . str_repeat('a', 256),
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image_url');
    }

    /** @test */
    public function 画像URLがURL形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image_url');
    }

    /** @test */
    public function 説明文が255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => str_repeat('a', 1001),
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function 説明文が文字列以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 123,
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function ジャンルが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => '',
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres');
    }


    /** @test */
    public function 存在しないジャンルを指定するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => ['fail'],
            'user_id' => $user->id,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function ユーザーが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => '',
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function 存在しないユーザーを指定するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => 999,
        ];

        Sanctum::actingAs($user);
        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function 未認証時は書籍が登録できず401が返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
            'user_id' => $user->id,
        ];

        $response = $this->postJson('/api/v1/books', $data);

        $response->assertStatus(401);
    }
}
