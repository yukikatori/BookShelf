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
            Review::factory()->count(2)->create([
                'book_id' => $book->id,
                'user_id' => $users->random()->id,
            ]);
        }

        // 残り10件をランダムに追加（各書籍に0～2件）
        $remainReviews = 10;

        while ($remainReviews > 0) {
            foreach ($books as $book) {
                if ($remainReviews <= 0) break;

                $extraCount = rand(0, 2);
                $extraCount = min($extraCount, $remainReviews);

                if ($extraCount > 0) {
                    Review::factory()
                        ->count($extraCount)
                        ->create([
                            'book_id' => $book->id,
                            'user_id' => $users->random()->id,
                        ]);
                    
                    $remainReviews -= $extraCount;
                }
            }
        }
    }
}
