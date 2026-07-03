<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\ReadingPlan;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();
        $selectedBooks = $books->random(6);
        $mainUser = User::first();

        // reading（三日以上前）、reading（三日前）、completed、reading（当日）、expired（期日三日後）、completed（期日三日後以降）のステータスを振り分け
        //  reading（三日以上前）、reading（三日前）、reading（当日）、expired（期日三日後）に対して通知が発生する
        $readingPlans = [
            ['book_id' => $selectedBooks[0]->id, 'target_date' => now()->subDays(4), 'completed_at' => now()->subDays(4), 'status' => 'completed'],
            ['book_id' => $selectedBooks[1]->id, 'target_date' => now()->subDays(3), 'status' => 'reading'],
            ['book_id' => $selectedBooks[2]->id, 'target_date' => now(), 'completed_at' => now(), 'status' => 'completed'],
            ['book_id' => $selectedBooks[3]->id, 'target_date' => now(), 'status' => 'reading'],
            ['book_id' => $selectedBooks[4]->id, 'target_date' => now()->addDays(3), 'status' => 'reading'],
            ['book_id' => $selectedBooks[5]->id, 'target_date' => now()->addDays(4), 'status' => 'reading'],
        ];

        // 主要シナリオ（ユーザー：山田太郎）
        foreach ($readingPlans as $plan) {
            ReadingPlan::firstOrCreate(
                [
                    'user_id' => $mainUser->id,
                    'book_id' => $plan['book_id'],
                    'target_date' => $plan['target_date'],
                ],
                [
                    'status' => $plan['status'],
                    'completed_at' => $plan['completed_at'] ?? null,
                ],
            );
        }

        // 認可判定のためその他ユーザーに2, 3件の読書計画を作成する
        $users = User::where('id', '!=', $mainUser->id)->get();

        foreach ($users as $user) {
            $selectedPlans = collect($readingPlans)->random(rand(2, 3));

            foreach ($selectedPlans as $plan) {
                ReadingPlan::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'book_id' => $plan['book_id'],
                        'target_date' => $plan['target_date'],
                    ],
                    [
                        'status' => $plan['status'],
                        'completed_at' => $plan['completed_at'] ?? null,
                    ],
                );
            }
        }
    }
}
