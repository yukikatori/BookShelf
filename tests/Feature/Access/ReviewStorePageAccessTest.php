<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;

class ReviewStorePageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログインが必要である旨のコメントが表示される(): void
    {
        $book = Book::factory()->create();

        $response = $this->get('/books/' . $book->id);

        $response->assertStatus(200);
        $response->assertSee('レビューを投稿するには');
    }
}
