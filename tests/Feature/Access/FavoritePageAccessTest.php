<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class FavoritePageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ログインユーザーのお気に入り書籍が登録日が新しい順に表示される(): void
    {
        $user = User::factory()->create(); 
        $book1 = Book::factory()->create(['created_at' => now()->subDay(3)]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $book3 = Book::factory()->create(['created_at' => now()->subDay()]);

        $user->favoriteBooks()->attach([$book1->id, $book2->id, $book3->id]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book3->title,
            $book2->title,
            $book1->title,
        ]);
    }

    /** @test */
    public function ログインユーザーのお気に入り書籍が書籍登録順、１０件／ページでページネーション表示される(): void
    {
        $user = User::factory()->create(); 
        $books = Book::factory()->count(15)->create();

        $user->favoriteBooks()->attach($books);

        $response = $this->actingAs($user)->get('favorites');

        $response->assertStatus(200);

        $response->assertViewHas('books', function ($viewBooks) {
            return $viewBooks->count() === 10;
        });

        $response->assertSee('page=2');
    }

    /** @test */
    public function 書籍タイトルを押すと書籍詳細に遷移する(): void
    {
        $user = User::factory()->create(); 
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book);

        $response = $this->actingAs($user)->get('favorites');

        $response->assertStatus(200);

        $detail = $this->get(route('books.show', $book));
        $detail->assertStatus(200);
        $detail->assertSee($book->title);
    }
}
