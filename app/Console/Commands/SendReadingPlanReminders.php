<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Notifications\ReadingPlanReminder;
use App\Models\ReadingPlan;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'send:reading-plan-reminders';

    public function handle()
    {
        $today = now()->startOfDay();

        $plans = ReadingPlan::where('status', '!=', 'completed')->get();

        foreach ($plans as $plan) {
            
            DB::transaction(function () use ($plan, $today) {
                
                $daysDiff = $today->diffInDays($plan->target_date, false);

                $timing = match (true) {
                    $daysDiff === 3 => 'three_days_before',
                    $daysDiff === 0 => 'on_due_date',
                    $daysDiff <= -3 => 'three_days_after',
                    default => null,
                };

                $alreadySent = $plan->user
                    ->notifications()
                    ->where('data->planId', $plan->id)
                    ->where('data->timing', $timing)
                    ->exists();

                if (! $alreadySent) {
                    $plan->user->notify(new ReadingPlanReminder(
                        bookTitle: $plan->book->title,
                        planId: $plan->id,
                        timing: $timing,
                    ));
                }

                if ($daysDiff < 0 && $plan->status !== 'expired') {
                    $plan->update(['status' => 'expired']);
                }
            });
        }

        $this->info('読書計画のリマインダー通知を送信しました');
    }
}
