<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class BookEditPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $book = Book::factory()->create();

        $response = $this->get('/books/' . $book->id . '/edit');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認可時には４０３Unauthorizedを表示する(): void
    {
        $owner = User::factory()->create(); 
        $user = User::factory()->create(); 

        $book = Book::factory()->create(['user_id' => $owner]);

        $response = $this->actingAs($user)->get('/books/' . $book->id . '/edit');

        $response->assertStatus(403);
    }

    /** @test */
    public function 既存データが初期値として表示された編集フォームが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'test',
            'author' => 'test',
            'isbn' => '1111111111111',
            'published_date' => '2026-06-09',
            'description' => 'test',
            'image_url' => 'http://test.com',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/books/' . $book->id . '/edit');

        $response->assertStatus(200);

        $response->assertSee($book->title);
        $response->assertSee($book->author);
        $response->assertSee($book->isbn);
        $response->assertSee($book->published_date);
        $response->assertSee($book->description);
        $response->assertSee($book->image_url);
    }
}
