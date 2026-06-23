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

        $commentsByRating = [
            1 => '期待外れでした',
            2 => '少し物足りなかったです',
            3 => '普通でした',
            4 => '面白かったです',
            5 => 'とても素晴らしかったです',
        ];

        foreach ($books as $book) {
            $selectedUsers = $users->random(rand(2, 4));

            foreach ($selectedUsers as $user) {
                $rating = rand(1, 5);

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $commentsByRating[$rating],
                ]);
            }
        }
    }
}
