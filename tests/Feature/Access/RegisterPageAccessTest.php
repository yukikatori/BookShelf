<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 名前・メール・パスワード・パスワード確認の入力フォームが表示される(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);

        $response->assertSee('お名前');
        $response->assertSee('メールアドレス');
        $response->assertSee('パスワード');
        $response->assertSee('パスワード確認');
    }

    /** @test */
    public function ログインボタンを押すとログイン画面へ遷移する(): void
    {
        $response = $this->get('/register');

        $detail = $this->get('/login');
        $detail->assertStatus(200);
        $detail->assertSee('メールアドレス');
        $detail->assertSee('パスワード');
    }
}
