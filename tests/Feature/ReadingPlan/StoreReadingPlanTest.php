<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class StoreReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画作成後、読書計画一覧へリダイレクトされ作成完了メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => now(),
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $this->assertDatabaseHas('reading_plans', [
            'book_id' => $book->id,
        ]);

        $response->assertRedirect('/reading-plans');
        $response->assertSessionHas('success', '読書計画を作成しました');
    }

    /** @test */
    public function 書籍を選択していない場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => '',
            'target_date' => now(),
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $response->assertSessionHasErrors('book_id');
    }

    /** @test */
    public function 期日を選択していない場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => '',
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 現在より過去の日付を指定した場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'book_id' => $book->id,
            'target_date' => '1999-12-31',
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $response->assertSessionHasErrors('target_date');
    }

    /** @test */
    public function 書籍に既に読書計画が存在する場合、バリデーションエラーが返る(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id
        ]);

        $data = [
            'book_id' => $book->id,
            'target_date' => now(),
        ];

        $response = $this->actingAs($user)->post('/reading-plans', $data);

        $response->assertSessionHasErrors('book_id');
    }
}
