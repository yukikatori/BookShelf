<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class NotificationPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 期日まで４日以上前の読書計画に対してリマインダーが通知される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(4),
        ]);

        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSee("「test」に読書計画が設定されています");
    }

    /** @test */
    public function 期日まで３日前の読書計画に対してリマインダーが通知される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
        ]);

        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSee("「test」の読了期限まであと3日です");
    }

    /** @test */
    public function 期日当日の読書計画に対してリマインダーが通知される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now(),
        ]);

        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSee("「test」の読了期限になりました");
    }

    /** @test */
    public function 期日から３日以上経過した読書計画に対してリマインダーが通知される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDays(3),
        ]);

        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSee("「test」の読了期限を3日過ぎています");
    }

    /** @test */
    public function 通知は新しい順に表示される(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['title' => 'old']);
        $book2 = Book::factory()->create(['title' => 'new']);

        $readingPlan1 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => now(),
        ]);

        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => now()->addDays(4),
        ]);

        $this->artisan('send:reading-plan-reminders');

        $notifications = $user->notifications;
        $notifications[0]->update(['created_at' => now()->subDay()]);
        $notifications[1]->update(['created_at' => now()]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSeeInOrder([
            $book2->title,
            $book1->title,
        ]);
    }
}
