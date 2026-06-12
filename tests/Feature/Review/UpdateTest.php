<?php

namespace Tests\Feature\Review;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビュー編集後、書籍詳細にリダイレクトする(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'test',
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'test',
        ]);

        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function 他人のレビューは編集できない(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'test',
        ];

        $response = $this->actingAs($user2)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function 編集完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'test',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put('/reviews/' . $review->id, $data);

        $response->assertStatus(200);
        $response->assertSee('レビューを編集しました');
    }

    /** @test */
    public function 評価が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => '',
            'comment' => 'test',
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function 評価が整数でない場合バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 'test',
            'comment' => 'test',
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function 評価が不正である場合（１～５以外）バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 99,
            'comment' => 'test',
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function コメントが未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '',
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');
    }

    /** @test */
    public function コメントが255文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => str_repeat('a', 256),
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');
    }

    /** @test */
    public function コメントが文字列でない場合バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 3,
        ];

        $response = $this->actingAs($user)->put('/reviews/' . $review->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');
    }
}
