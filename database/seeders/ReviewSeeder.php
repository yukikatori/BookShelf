<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        // まず、全書籍に2件ずつレビューを投稿（22件分）
        foreach ($books as $book) {
            $selectedUsers = $users->random(2);

            foreach ($selectedUsers as $user) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // 残り10件をランダムに追加（各書籍に0～2件）
        $remainReviews = 10;

        while ($remainReviews > 0) {
            foreach ($books as $book) {
                if ($remainReviews <= 0) break;

                $currentCount = Review::where('book_id', $book->id)->count();

                if ($currentCount >= 4) {
                    continue;
                }

                $maxAddable = 4 - $currentCount;

                $reviewedUserIds = Review::where('book_id', $book->id)->pluck('user_id');
                $availableUsers = $users->whereNotIn('id', $reviewedUserIds);

                $extraCount = rand(0, 2);
                $extraCount = min($extraCount, $maxAddable, $remainReviews);

                if ($extraCount > 0) {
                    foreach ($availableUsers->random($extraCount) as $user) {
                        Review::factory()->create([
                            'book_id' => $book->id,
                            'user_id' => $user->id,
                        ]);
                    }

                    $remainReviews -= $extraCount;
                }
            }
        }
    }
}
