<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\User;

class BookStorePageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function タイトル・著者・ISBN・出版日・説明・画像URLの入力欄、ジャンル一覧、ISBN検索が表示される(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/books/create');

        $response->assertStatus(200);

        $response->assertSee('タイトル');
        $response->assertSee('著者');
        $response->assertSee('ISBN');
        $response->assertSee('出版日');
        $response->assertSee('説明');
        $response->assertSee('画像URL');
        $response->assertSee('ISBN から書籍情報を自動入力');

        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }
}
