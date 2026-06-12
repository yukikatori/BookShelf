<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインが実施でき、書籍一覧へリダイレクトされる(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => '12345678',
        ];

        $response = $this->post('/login', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function メールアドレスが未入力の場合バリデーションエラーが返る(): void
    {
        $data = [
            'email' => '',
            'password' => '12345678',
        ];

        $response = $this->post('/login', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションエラーが返る(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $response = $this->post('/login', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 入力情報が誤っている場合バリデーションエラーが返る(): void
    {
        $data = [
            'email' => 'fail@example.com',
            'password' => '34567812',
        ];

        $response = $this->post('/login', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
}
