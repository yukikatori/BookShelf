<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class EditReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画編集後、読書計画一覧へリダイレクトされ作成完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'target_date' => now()->addDays(7),
        ];

        $response = $this->actingAs($user)->put('/reading-plans/' . $readingPlan->id, $data);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => now()->addDays(7),
        ]);

        $response->assertRedirect('/reading-plans');
        $response->assertSessionHas('success', '読書計画を編集しました');
    }   

    /** @test */
    public function 期日を選択していない場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $data = [
            'target_date' => '',
        ];

        $response = $this->actingAs($user)->put('/reading-plans/' . $readingPlan->id, $data);

        $response->assertSessionHasErrors('target_date');
    } 

    /** @test */
    public function 現在より過去の日付を指定した場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now(),
        ]);

        $data = [
            'target_date' => now()->subDays(),
        ];

        $response = $this->actingAs($user)->put('/reading-plans/' . $readingPlan->id, $data);

        $response->assertSessionHasErrors('target_date');
    } 
}
