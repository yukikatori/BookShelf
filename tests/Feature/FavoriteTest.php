<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お気に入りボタンを押すことで、お気に入り登録され、書籍詳細にリダイレクトされる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post('/books/' . $book->id . '/favorites');

        $response->assertStatus(302);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function お気に入り登録された状態でお気に入りボタンを押すことで、お気に入りが解除され、書籍詳細にリダイレクトされる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->post('/books/' . $book->id . '/favorites');

        $response->assertStatus(302);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect('/books/' . $book->id);
    }

    /** @test */
    public function 未認証時にお気に入りボタンを押すと、ログイン画面にリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post('/books/' . $book->id . '/favorites');

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }
}
