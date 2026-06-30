<?php

namespace Tests\Feature\Notification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Carbon\Carbon;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 「既読にする」を押下すると通知が既読になる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDay(),
        ]);

        $user->notify(new ReadingPlanReminder(
            $book->title,
            $readingPlan->id
        ));

        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user)->post('/notifications/' . $notification->id . '/read');

        $this->assertNotNull($user->fresh()->notifications()->find($notification->id)->read_at);

        $response->assertRedirect('/notifications');
        $response->assertSessionHas('success', '通知を既読にしました');
    }
    
    /** @test */
    public function 日付が変わるタイミングで通知が送信される(): void
    {
        Carbon::setTestNow('2026-07-01 23:50:00');

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-07-02',
        ]);

        Carbon::setTestNow('2026-07-02 00:00:01');
        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertSee("「test」の読了期限になりました");
    }

    /** @test */
    public function 一度送信された種別の通知は送信されないが、異なる種別の通知は送信される(): void
    {
        // 読了期日3日前に読書計画を作成
        Carbon::setTestNow('2026-06-29 00:00:01');

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-07-02',
        ]);

        // 1回目の通知送信と件数確認→期日三日前の通知
        $this->artisan('send:reading-plan-reminders');
        $this->assertCount(1, $user->fresh()->notifications);

        // 3日時間を進めて通知送信と件数確認→期日当日の通知
        Carbon::setTestNow('2026-07-02 00:00:01');
        $this->artisan('send:reading-plan-reminders');
        $this->assertCount(2, $user->fresh()->notifications);

        // 1日時間を進めて通知送信と件数確認→通知は増えていないことの確認
        Carbon::setTestNow('2026-07-03 00:00:01');
        $this->artisan('send:reading-plan-reminders');
        $this->assertCount(2, $user->fresh()->notifications);

        // 2日時間を進めて通知送信と件数確認→期日超過の通知
        Carbon::setTestNow('2026-07-05 00:00:01');
        $this->artisan('send:reading-plan-reminders');
        $this->assertCount(3, $user->fresh()->notifications);

        $response = $this->actingAs($user)->get('/notifications');

        // 時間を経過させたことで重複はないが、異なる種別の通知は送信される
        $response->assertSee("「test」の読了期限まであと3日です");
        $response->assertSee("「test」の読了期限になりました");
        $response->assertSee("「test」の読了期限を3日過ぎています");
    }

    /** @test */
    public function 期限切れの読書計画に対しては、通知を実施し読書計画の状態を変更する(): void
    {
        Carbon::setTestNow('2026-06-29 00:00:01');

        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'test']);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-06-29',
        ]);

        // 3日時間を進めて通知送信
        Carbon::setTestNow('2026-07-02 00:00:01');
        $this->artisan('send:reading-plan-reminders');

        $response = $this->actingAs($user)->get('/notifications');

        $this->assertEquals(ReadingPlanStatus::Expired, $readingPlan->fresh()->status);
        $response->assertSee("「test」の読了期限を3日過ぎています");
    }
}
