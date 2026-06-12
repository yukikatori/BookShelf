<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenreShowPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get('/genres/' . $genre->id);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ジャンル名と紐づく書籍が登録が新しい順で表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book1 = Book::factory()->create(['created_at' => now()->subDay()]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $book3 = Book::factory()->create(['created_at' => now()->subDay(3)]);

        $genre->books()->attach([$book1->id, $book2->id, $book3->id]);

        $response = $this->actingAs($user)->get('/genres/' . $genre->id);

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
            $book3->title,
        ]);
    }

    /** @test */
    public function ジャンル名と紐づく書籍がページネーション（１０件／ページ）表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(15)->create();

        $genre->books()->attach($books);

        $response = $this->actingAs($user)->get('/genres/' . $genre->id);

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
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book);

        $response = $this->actingAs($user)->get('/genres/' . $genre->id);

        $response->assertStatus(200);

        $detail = $this->actingAs($user)->get(route('books.show', $book));
        $detail->assertStatus(200);
        $detail->assertSee($book->title);
    }
}
