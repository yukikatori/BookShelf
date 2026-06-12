<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録ができ、登録後に書籍一覧へリダイレクトされる(): void
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue(
            Hash::check('12345678', User::where('email', 'test@example.com')->first()->password)
        );
    }

    /** @test */
    public function お名前が未入力の場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function メールアドレスが未入力の場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => 'test',
            'email' => '',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function メールアドレスがメール形式でない場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => 'test',
            'email' => '123',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function メールアドレスが既に使用されている場合バリデーションエラーが返る(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function パスワードが8文字未満の場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 確認用パスワードが一致しない場合バリデーションエラーが返る(): void
    {
        $data = [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '23456781',
        ];

        $response = $this->post('/register', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }
}
