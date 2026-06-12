<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メール・パスワードの入力フォームが表示される(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        $response->assertSee('メールアドレス');
        $response->assertSee('パスワード');
    }

    /** @test */
    public function 会員登録ボタンを押すと会員登録画面へ遷移する(): void
    {
        $response = $this->get('/login');

        $detail = $this->get('/register');
        $detail->assertStatus(200);
        $detail->assertSee('お名前');
        $detail->assertSee('メールアドレス');
        $detail->assertSee('パスワード');
        $detail->assertSee('パスワード確認');
    }

    /** @test */
    public function ログイン済みでログイン画面へアクセスすると書籍一覧へリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }
}
