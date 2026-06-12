<?php

namespace Tests\Feature\Api\v1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class IndexBookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function json形式の一覧が返り、ページネーションが機能する(): void
    {
        $books = Book::factory()->count(15)->create();
        $genres = Genre::factory()->count(5)->create();

        foreach ($books as $book) {
            $selectedGenres = $genres->random(rand(2,3))->pluck('id');
        
            $book->genres()->attach($selectedGenres);
        }

        foreach ($books as $book) {
            Review::factory()->count(rand(2,5))->create([
                'book_id' => $book->id,
            ]);
        }

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $this->assertCount(10, $json['data']);

        $this->assertEquals(1, $json['meta']['current_page']);
        $this->assertEquals(10, $json['meta']['per_page']);
        $this->assertEquals(15, $json['meta']['total']);
        $this->assertEquals(2, $json['meta']['last_page']);
    }

    /** @test */
    public function キーワード検索（タイトル・著者）後_json形式の一覧が返り、ページネーションが機能する(): void
    {
        $book1 = Book::factory()->create([
            'title' => 'test',
        ]);

        $book2 = Book::factory()->create([
            'author' => 'test',
        ]);

        $book3 = Book::factory()->create([
            'title' => 'fail',
            'author' => 'fail',
        ]);

        $genre = Genre::factory()->create();

        $book1->genres()->attach($genre);
        $book2->genres()->attach($genre);
        $book3->genres()->attach($genre);

        $response = $this->getJson('/api/v1/books?keyword=test');

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $this->assertCount(2, $json['data']);

        $response->assertJsonMissing(['title' => 'fail']);
        $response->assertJsonMissing(['author' => 'fail']);
    }

    /** @test */
    public function キーワードが255文字を超えるとバリデーションエラーが返る(): void
    {
        $response = $this->getJson('/api/v1/books?keyword=' . str_repeat('a', 256));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('keyword');
    }

    /** @test */
    public function ジャンル絞り込み後_json形式の一覧が返り、ページネーションが機能する(): void
    {
        $book1 = Book::factory()->create([]);
        $book2 = Book::factory()->create([]);
        $book3 = Book::factory()->create([]);

        $genre1 = Genre::factory()->create([
            'name' => 'test',
        ]);
        
        $genre2 = Genre::factory()->create([
            'name' => 'fail',
        ]);

        $book1->genres()->attach($genre1);
        $book2->genres()->attach($genre1);
        $book3->genres()->attach($genre2);

        $response = $this->getJson('/api/v1/books?genres[0]=test');

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $this->assertCount(2, $json['data']);

        $response->assertJsonMissing(['genre' => 'test2']);
    }

    /** @test */
    public function ジャンルが存在しない場合、バリデーションエラーが返る(): void
    {
        $response = $this->getJson('/api/v1/books?genres[0]=none');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function ソート条件「登録日が新しい順」が機能する(): void
    {
        $book1 = Book::factory()->create(['created_at' => now()->subDay()]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $book3 = Book::factory()->create(['created_at' => now()->subDay(3)]);

        $genre = Genre::factory()->create();

        $book1->genres()->attach($genre);
        $book2->genres()->attach($genre);
        $book3->genres()->attach($genre);

        $response = $this->getJson('/api/v1/books?sort=latest');

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $titles = array_column($json['data'], 'title');

        $expected = [
            $book1->title,
            $book2->title,
            $book3->title,
        ];

        $this->assertEquals($expected, $titles);
    }

    /** @test */
    public function ソート条件「登録日が古い順」が機能する(): void
    {
        $book1 = Book::factory()->create(['created_at' => now()->subDay()]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $book3 = Book::factory()->create(['created_at' => now()->subDay(3)]);

        $genre = Genre::factory()->create();

        $book1->genres()->attach($genre);
        $book2->genres()->attach($genre);
        $book3->genres()->attach($genre);

        $response = $this->getJson('/api/v1/books?sort=oldest');

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $titles = array_column($json['data'], 'title');

        $expected = [
            $book3->title,
            $book2->title,
            $book1->title,
        ];

        $this->assertEquals($expected, $titles);
    }

    /** @test */
    public function ソート条件「評価が高い順」が機能する（レビューがない書籍は新しい順で最後に表示）(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();
        $book4 = Book::factory()->create(['created_at' => now()->subday()]);

        $genre = Genre::factory()->create();

        $book1->genres()->attach($genre);
        $book2->genres()->attach($genre);
        $book3->genres()->attach($genre);
        $book4->genres()->attach($genre);

        Review::factory()->count(3)->create([
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->count(3)->create([
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        $response = $this->getJson('/api/v1/books?sort=rating');


        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);

        $titles = array_column($json['data'], 'title');

        $expected = [
            $book1->title,
            $book2->title,
            $book3->title,
            $book4->title,
        ];

        $this->assertEquals($expected, $titles);
    }

    /** @test */
    public function ソート条件が存在しない場合、バリデーションエラーが返る(): void
    {
        $response = $this->getJson('/api/v1/books?sort=fail');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('sort');
    }
}
