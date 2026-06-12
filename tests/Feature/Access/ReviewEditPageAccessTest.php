<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewEditPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->get('/reviews/' . $review->id . '/edit');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認可時には４０３Unauthorizedを表示(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user2)->get('/reviews/' . $review->id . '/edit');

        $response->assertStatus(403);
    }

    /** @test */
    public function 既存の評価・コメントが初期値として表示された編集フォームが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get('/reviews/' . $review->id . '/edit');

        $response->assertStatus(200);

        $response->assertSee($review->rating);
        $response->assertSee($review->comment);
    }
}
