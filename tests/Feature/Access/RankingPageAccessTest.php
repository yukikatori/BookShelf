<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;

class RankingPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍がレビュー平均評価降順で表示される(): void
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $review1 = Review::factory()->count(3)->create([
            'book_id' => $book1->id,
            'rating' => 5,
        ]);
        
        $review2 = Review::factory()->count(3)->create([
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
        ]);
    }

    /** @test */
    public function レビュー平均評価のTOP10書籍が表示される(): void
    {
        $books = Book::factory()->count(15)->create();

        foreach ($books as $book) {
            Review::factory()->count(3)->create([
                'book_id' => $book->id,
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $top10 = $books
            ->sortByDesc(fn($book) => $book->reviews()->avg('rating'))
            ->take(10);

        foreach ($top10 as $book) {
            $response->assertSee($book->title);
        }
    }

    /** @test */
    public function レビューのない書籍は表示されない(): void
    {
        $noReviewBooks = Book::factory()->count(10)->create();

        $reviewedBooks = Book::factory()->count(3)->create();

        foreach ($reviewedBooks as $book) {
            Review::factory()->count(2)->create([
                'book_id' => $book->id,
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        foreach ($reviewedBooks as $book) {
            $response->assertSee($book->title);
        }

        foreach ($noReviewBooks as $book) {
            $response->assertDontSee($book->title);
        }
    }

    /** @test */
    public function ゲストがランキング画面にアクセスできる(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
    }

    /** @test */
    public function 書籍タイトルを押すと書籍詳細に遷移する(): void
    {
        $books = Book::factory()->count(10)->create();

        foreach ($books as $book) {
            Review::factory()->count(3)->create([
                'book_id' => $book->id,
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        foreach ($books as $book) {
            $detail = $this->get(route('books.show', $book));
            $detail->assertStatus(200);
            $detail->assertSee($book->title);
        }
    }
}
