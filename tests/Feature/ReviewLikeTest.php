<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function いいねボタンを押すことで、いいね登録され、書籍詳細にリダイレクトされる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->post('/reviews/' . $review->id . '/like');

        $response->assertStatus(302);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function いいね登録された状態でいいねボタンを押すことで、いいねが解除され、書籍詳細にリダイレクトされる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)->post('/reviews/' . $review->id . '/like');

        $response->assertStatus(302);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function 未認証時にお気に入りボタンを押すと、ログイン画面にリダイレクトされる(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->post('/reviews/' . $review->id . '/like');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }
}
