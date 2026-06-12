<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つのレビューが特定のユーザーに紐づく(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /** @test */
    public function １つのレビューが特定の本に紐づく(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /** @test */
    public function 中間テーブルを介して、１つのレビューが複数のユーザーに紐づいている(): void
    {
        $users = User::factory()->count(3)->create();
        $book = Book::factory()->create();
        
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $review->likedByUsers()->attach($users->pluck('id'));
        
        foreach ($users as $user) {
            $this->assertDatabaseHas('review_likes', [
                'review_id' => $review->id,
                'user_id' => $user->id,
            ]);
        }

        $this->assertCount(3, $review->likedByUsers);
        $this->assertInstanceOf(User::class, $review->likedByUsers->first());
    }
}
