<?php

namespace Tests\Feature\ReadingPlan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class IndexReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画は新しい順で表示される(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'reading',
            'created_at' => now(),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'reading',
            'created_at' => now()->subDays(),
        ]);

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
        ]);
    }

    /** @test */
    public function 状態「進行中」で読書計画の絞り込みができる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'reading',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/reading-plans?status=reading');

        $response->assertSee($book1->title);
        $response->assertDontSee($book2->title);
    }

    /** @test */
    public function 状態「読了」で読書計画の絞り込みができる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'reading',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get('/reading-plans?status=completed');

        $response->assertSee($book2->title);
        $response->assertDontSee($book1->title);
    }

    /** @test */
    public function 状態「期限切れ」で読書計画の絞り込みができる(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'reading',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'expired',
        ]);

        $response = $this->actingAs($user)->get('/reading-plans?status=expired');

        $response->assertSee($book2->title);
        $response->assertDontSee($book1->title);
    }

    /** @test */
    public function 「読了する」を押すことで読了状態へ変更し、メッセージを表示する(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user)->post('/reading-plans/' . $readingPlan->id . '/complete');

        $this->assertEquals(ReadingPlanStatus::Completed, $readingPlan->fresh()->status);

        $response->assertRedirect('/reading-plans');
        $response->assertSessionHas('success', '読書計画の状態を「読了」に変更しました');
    }

    /** @test */
    public function 未認証時に「読了する」をおすとログイン画面へリダイレクトする(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->post('/reading-plans/' . $readingPlan->id . '/complete');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認可時には「読了する」をおすと４０３Unauthorizedを表示(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user2)->post('/reading-plans/' . $readingPlan->id . '/complete');

        $response->assertStatus(403);
    }

    /** @test */
    public function 未認証時に「削除」をおすとログイン画面へリダイレクトする(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->delete('/reading-plans/' . $readingPlan->id);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認可時には「削除」をおすと４０３Unauthorizedを表示(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user2)->delete('/reading-plans/' . $readingPlan->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function 「削除」を押すことで読書計画が削除でき、メッセージを表示する(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'reading',
        ]);

        $response = $this->actingAs($user)->delete('/reading-plans/' . $readingPlan->id);

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);

        $response->assertRedirect('/reading-plans');
        $response->assertSessionHas('success', '読書計画を削除しました');
    }
}
