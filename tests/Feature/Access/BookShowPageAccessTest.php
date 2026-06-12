<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookShowPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍タイトル・著者・ISBN・出版日・説明・画像・ジャンル・レビュー一覧・いいね数が表示される(): void
    {
        $users = User::factory()->count(3)->create();
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $reviews = Review::factory()->count(3)->create([
            'book_id' => $book->id
        ]);

        $book->genres()->attach($genres);

        foreach ($users as $user) {
            $selectedReviews = $reviews->random(rand(1,2))->pluck('id');

            $user->likedReviews()->attach($selectedReviews);
        }

        $response = $this->get('/books/' . $book->id);

        $response->assertStatus(200);

        $response->assertViewHas('book', function ($viewBook) use ($book, $genres, $reviews) {
            return $viewBook->id === $book->id
                && $viewBook->genres->pluck('id')->sort()->values()->all() === $genres->pluck('id')->sort()->values()->all()
                && $viewBook->reviews->pluck('id')->sort()->values()->all() === $reviews->pluck('id')->sort()->values()->all();
        });

        foreach ($reviews as $review) {
            $likeCount = $review->likedByUsers()->count();
            $response->assertSee($likeCount);
        }
    }

    /** @test */
    public function ゲストが書籍詳細にアクセスできる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get('/books/' . $book->id);

        $response->assertStatus(200);
    }
}
