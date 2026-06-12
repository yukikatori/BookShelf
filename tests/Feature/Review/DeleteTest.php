<?php

namespace Tests\Feature\Review;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の登録したレビューを削除することができる、関連するいいねも削除される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)->delete('/reviews/' . $review->id);

        $response->assertStatus(302);
        
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 削除完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete('/reviews/' . $review->id);

        $response->assertStatus(200);
        $response->assertSee('レビューを削除しました');
    }

    /** @test */
    public function 削除後に書籍詳細にリダイレクトする(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->delete('/reviews/' . $review->id);

        $response->assertStatus(302);
        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function 他人の登録したレビューは削除できない(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user2)->delete('/reviews/' . $review->id);

        $response->assertStatus(403);
    }
}
