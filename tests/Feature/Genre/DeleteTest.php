<?php

namespace Tests\Feature\Genre;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンルを削除することができ、ジャンル一覧にリダイレクトする(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete('/genres/' . $genre->id);

        $response->assertStatus(302);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);

        $response->assertRedirect('/genres');
    }

    /** @test */
    public function 削除完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete('/genres/' . $genre->id);

        $response->assertStatus(200);
        $response->assertSee('ジャンルを削除しました');
    }

    /** @test */
    public function 書籍が紐づいているときはジャンル一覧へリダイレクトし、エラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->delete('/genres/' . $genre->id);

        $response->assertStatus(200);
        $response->assertSee('このジャンルには書籍が紐づいているので削除できません');
    }
}
