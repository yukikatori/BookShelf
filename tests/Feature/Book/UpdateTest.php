<?php

namespace Tests\Feature\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍が編集後、ジャンルが紐づけられ書籍詳細画面にリダイレクトする(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertRedirect('/books/' . $book->id);

        $this->assertDatabaseHas('books', [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
        ]);

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    /** @test */
    public function 編集完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put('/books/' . $book->id, $data);

        $response->assertStatus(200);
        $response->assertSee('書籍を更新しました');
    }

    /** @test */
    public function タイトルが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => '',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function タイトルが文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 123,
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function タイトルが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => str_repeat('a', 256),
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function 著者が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => '',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function 著者が文字列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 123,
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function 著者が255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => str_repeat('a', 256),
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function ISBNが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function ISBNが13桁以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '11111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function ISBNコードが既存のISBNコードと重複するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        
        $book1 = Book::factory()->create([
            'isbn' => '1111111111111',
        ]);

        $book2 = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book2->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function ISBNコードが既存のISBNコードと重複するとバリデーションエラーが返る（自身は除く）(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
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
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertRedirect('/books/' . $book->id);

        $this->assertDatabaseHas('books', [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
        ]);

        foreach ($genres as $genre) {
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
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('published_date');
    }

    /** @test */
    public function 出版日が日付形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => 'test',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('published_date');
    }

    /** @test */
    public function 画像URLが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg' . str_repeat('a', 256),
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('image_url');
    }

    /** @test */
    public function 画像URLがURL形式以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('image_url');
    }

    /** @test */
    public function 説明文が1000文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => str_repeat('a', 1001),
            'image_url' => 'http://example.com/test.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function 説明文が文字列以外だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 123,
            'image_url' => 'test',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function ジャンルが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => '',
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('genres');
    }

    /** @test */
    public function ジャンルが配列でなければバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 123,
            'image_url' => 'test',
            'genres' => 123,
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('genres');
    }

    /** @test */
    public function 存在しないジャンルを指定するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-10',
            'description' => 'test',
            'image_url' => 'test',
            'genres' => [999],
        ];

        $response = $this->actingAs($user)->put('/books/' . $book->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('genres.0');
    }
}

