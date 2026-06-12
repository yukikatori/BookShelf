<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenreEditPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get('/genres/' . $genre->id . '/edit');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ジャンルが書籍に紐づいている場合、リダイレクトされエラーが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->actingAs($user)
        ->followingRedirects()
        ->get('/genres/' . $genre->id . '/edit');

        $response->assertSee('このジャンルには書籍が紐づいているので編集できません');
    }

    /** @test */
    public function 現在のジャンル名が初期値として表示された編集フォームが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get('/genres/' . $genre->id . '/edit');

        $response->assertStatus(200);

        $response->assertSee($genre->name);
    }
}
