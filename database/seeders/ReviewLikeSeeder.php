<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {
            $likeUsers = $users->where('id', '!=', $review->user_id);
            $selected = $likeUsers->random(rand(0, min(3, $likeUsers->count())))
                                  ->pluck('id')
                                  ->toArray();
            $review->likedBy()->syncWithoutDetaching($selected);
        }
    }
}
