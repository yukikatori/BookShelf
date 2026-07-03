<?php

namespace Tests\Feature\Api\v1\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレス未入力の場合は422が返る(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => '',
            'password' => '12345678',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function パスワード未入力の場合は422が返る(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function 認証情報が誤っている場合は401が返る(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'fail@example.com',
            'password' => '23456781',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function 正しい認証情報の場合はトークンが返る(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        $token = $response->json('token');
        $this->assertNotEmpty($token);
    }
}
