<?php

namespace Tests\Feature\Genre;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class UpdateTest extends TestCase
{
     use RefreshDatabase;

    /** @test */
    public function ジャンル編集後、ジャンル一覧にリダイレクトする(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre->id, $data);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('genres', [
            'name' => 'test',
        ]);

        $response->assertRedirect('/genres');
    }

    /** @test */
    public function 編集完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put('/genres/' . $genre->id, $data);

        $response->assertStatus(200);
        $response->assertSee('ジャンル名を編集しました');
    }

    /** @test */
    public function 書籍が紐づいているときはジャンル一覧へリダイレクトし、エラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $book->genres()->attach($genre->id);
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->put('/genres/' . $genre->id, $data);

        $response->assertStatus(200);
        $response->assertSee('このジャンルには書籍が紐づいているので編集できません');
    }

    /** @test */
    public function ジャンル名が未入力だとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        
        $data = [
            'name' => '',
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が20文字を超えるとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        
        $data = [
            'name' => str_repeat('a', 21),
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が文字列でない場合バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        
        $data = [
            'name' => 1234,
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が既存のジャンル名と重複するとバリデーションエラーが返る(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => 'after',
        ]);
        $genre2 = Genre::factory()->create([
            'name' => 'before',
        ]);
        
        $data = [
            'name' => 'after',
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre2->id, $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル名が既存のジャンル名と重複するとバリデーションエラーが返る（自身は除く）(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'test',
        ]);
        
        $data = [
            'name' => 'test',
        ];

        $response = $this->actingAs($user)->put('/genres/' . $genre->id, $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('genres', [
            'name' => 'test',
        ]);
    }
}
