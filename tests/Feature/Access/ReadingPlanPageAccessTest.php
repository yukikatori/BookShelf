<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 書籍、期日、完了日、状態、操作が表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertSeeText('書籍');
        $response->assertSeeText('期日');
        $response->assertSeeText('完了日');
        $response->assertSeeText('状態');
        $response->assertSeeText('操作');
    }
}
