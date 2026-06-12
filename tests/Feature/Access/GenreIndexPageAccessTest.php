<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class GenreIndexPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function ジャンル登録が新しい順でジャンル一覧が表示される(): void
    {
        $user = User::factory()->create();
        $genre1 = Genre::factory()->create(['created_at' => now()->subDay()]);
        $genre2 = Genre::factory()->create(['created_at' => now()->subDay(2)]);
        $genre3 = Genre::factory()->create(['created_at' => now()->subDay(3)]);

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $genre1->name,
            $genre2->name,
            $genre3->name,
        ]);
    }

    /** @test */
    public function ジャンルに紐づく書籍数付きでジャンル一覧が表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();
        $genre = Genre::factory()->create();

        $genre->books()->attach($books);

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);

        $response->assertSee($genre->books()->count());
    }

}
