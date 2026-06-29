<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\User;

class ReadingPlanStorePageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/reading-plans/create');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 書籍プルダウンと期日入力フォームが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);
        
        $response = $this->actingAs($user)->get('/reading-plans/create');

        $response->assertSee('書籍');
        $response->assertSee('name="book_id"', false);
        $response->assertSee('test');

        $response->assertSee('期日');
        $response->assertSee('name="target_date"', false);
        $response->assertSee('type="date"', false);
    }
}
