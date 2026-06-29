<?php

namespace Tests\Feature\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワード検索（タイトル・著者）実施後ページネーションが機能する(): void
    {
        $targetBooks = Book::factory()->count(15)->create([
            'title' => 'test',
            'author' => 'test',
        ]);

        $otherBooks = Book::factory()->count(5)->create([
            'title' => 'dummy',
            'author' => 'dummy',
        ]);

        $response = $this->get('/?keyword=test');

        $response->assertSee('page=2');

        $books = $response->original->getData()['books'];
        $this->assertCount(10, $books);

        $response->assertDontSee('dummy');
    }

    /** @test */
    public function ジャンル絞り込み後ページネーションが機能する(): void
    {
        $targetBooks = Book::factory()->count(15)->create([
            'title' => 'test',
            'author' => 'test',
        ]);

        $otherBooks = Book::factory()->count(5)->create([
            'title' => '小説',
            'author' => '小説',
        ]);

        $targetGenre = Genre::factory()->create(['id' => 1, 'name' => 'test']);
        $anotherGenre = Genre::factory()->create(['id' => 2, 'name' => '小説']);

        foreach ($targetBooks as $book) {
            $book->genres()->attach($targetGenre->id);
        }

        foreach ($otherBooks as $book) {
            $book->genres()->attach($anotherGenre->id);
        }

        $response = $this->get('/?genre=1');

        $response->assertSee('page=2');

        $books = $response->original->getData()['books'];
        $this->assertCount(10, $books);

        foreach ($books as $book) {
            $this->assertSame('test', $book->title);
        }    
    }

    /** @test */
    public function ソート条件「登録日が新しい順」が機能する(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get('/?sort=newest');

        $response->assertSeeInOrder([
            $newBook->title,
            $oldBook->title,
        ]);
    }

    /** @test */
    public function ソート条件「登録日が古い順」が機能する(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get('/?sort=oldest');

        $response->assertSeeInOrder([
            $oldBook->title,
            $newBook->title,
        ]);
    }
    
    /** @test */
    public function ソート条件「タイトル昇順」が機能する(): void
    {
        $book1 = Book::factory()->create([
            'title' => 'a',
        ]);

        $book2 = Book::factory()->create([
            'title' => 'b',
        ]);

        $response = $this->get('/?sort=title');

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
        ]);
    }

    /** @test */
    public function ソート条件「評価が高い順」が機能する（レビューがない書籍は新しい順で最後に表示）(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book3 = Book::factory()->create([
            'created_at' => now(),
        ]);

        $book4 = Book::factory()->create([
            'created_at' => now()->subDays(),
        ]);

        $highRatedReview = Review::factory()->create([
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        $lowRatedReview = Review::factory()->create([
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        $response = $this->get('/?sort=rating');

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
            $book3->title,
            $book4->title,
        ]);
    }

    /** @test */
    public function ページネーションリンクを押しても、検索条件を維持する(): void
    {
        $targetBooks = Book::factory()->count(15)->create([
            'title' => 'test',
            'author' => 'test',
        ]);

        $otherBooks = Book::factory()->count(5)->create([
            'title' => 'dummy',
            'author' => 'dummy',
        ]);

        $response = $this->get('/?keyword=test');

        $response->assertSeeInOrder([
            'keyword=test',
            'page=2',
        ]);
    }
}
