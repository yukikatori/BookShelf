<?php

namespace Tests\Feature\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の登録した書籍を削除することができ、関連データ（レビュー・お気に入り・ジャンル紐付け）も削除される(): void
    {
        $user = User::factory()->create();
        
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $genres = Genre::factory()->count(2)->create();

        $user->favoriteBooks()->attach($book->id);
        $book->genres()->attach($genres->pluck('id'));

        $response = $this->actingAs($user)->delete('/books/' . $book->id);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

         $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 削除完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete('/books/' . $book->id);

        $response->assertStatus(200);
        $response->assertSee('書籍を削除しました');
    }

    /** @test */
    public function 削除後に書籍一覧画面にリダイレクトする(): void
    {
        $user = User::factory()->create();
        
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete('/books/' . $book->id);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function 他人の登録した書籍は削除できない(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user1->id,
        ]);

        $response = $this->actingAs($user2)->delete('/books/' . $book->id);

        $response->assertStatus(403);
    }
}
