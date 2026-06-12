<?php

namespace Tests\Feature\Genre;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\User;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンル登録後、ジャンル一覧にリダイレクトする(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('genres', [
            'name' => 'test',
        ]);

        $response->assertRedirect('/genres');
    }

    /** @test */
    public function 登録完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post('/genres', $data);

        $response->assertStatus(200);
        $response->assertSee('ジャンルを登録しました');
    }

    /** @test */
    public function ジャンル名が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => '',
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が20文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => str_repeat('a', 21),
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が文字列でない場合バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        
        $data = [
            'name' => 1234,
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が既存のジャンル名と重複するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'test',
        ]);
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)->post('/genres', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }
}
