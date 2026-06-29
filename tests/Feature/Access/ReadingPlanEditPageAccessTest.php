<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanEditPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->get('/reading-plans/'. $readingPlan->id . '/edit');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認可時には403Unauthorizedを表示(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user2)->get('/reading-plans/'. $readingPlan->id . '/edit');
        
        $response->assertStatus(403);
    }

    /** @test */
    public function 既存の期日が初期値として表示された編集フォームが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-06-28'
        ]);

        $response = $this->actingAs($user)->get('/reading-plans/'. $readingPlan->id . '/edit');

        $response->assertSee($book->title);
        $response->assertSee($readingPlan->status->label());
        $response->assertSee('value="2026-06-28"', false);
    }
}
