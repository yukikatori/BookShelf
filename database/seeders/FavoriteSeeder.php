<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();
        $users = User::all();

        foreach ($users as $user) {
            $selected = $books->random(rand(3, min(5, $books->count())))
                              ->pluck('id')
                              ->toArray();

            $user->favoriteBook()->syncWithoutDetaching($selected);
        }
    }
}

