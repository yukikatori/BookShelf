<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １人のユーザーから紐づく複数の書籍が取得できる(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->books);
        $this->assertInstanceOf(Book::class, $user->books->first());
    }

    /** @test */
    public function １人のユーザーから紐づく複数のレビューが取得できる(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();

        foreach ($books as $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $this->assertCount(3, $user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    /** @test */
    public function 中間テーブルを介して、１人のユーザーが複数の書籍に紐づいている(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();

        $user->favoriteBooks()->attach($books->pluck('id'));

        foreach ($books as $book) {
            $this->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $this->assertCount(3, $user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());
    }

    /** @test */
    public function 中間テーブルを介して、１人のユーザーが複数のレビューに紐づいている(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();

        $reviews = collect();
        foreach ($books as $book) {
            $reviews->push(
                Review::factory()->create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                ])
            );
        }

        $user->likedReviews()->attach($reviews->pluck('id'));

        foreach ($reviews as $review) {
            $this->assertDatabaseHas('review_Likes', [
                'user_id' => $user->id,
                'review_id' => $review->id,
            ]);
        }

        $this->assertCount(3, $user->likedReviews);
        $this->assertInstanceOf(Review::class, $user->likedReviews->first());
    }
}
