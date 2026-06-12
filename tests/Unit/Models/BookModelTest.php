<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つの書籍が特定のユーザーに紐づく(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $book->user->id);
        $this->assertInstanceOf(User::class, $book->user);
    }

    /** @test */
    public function 中間テーブルを介して、1つの書籍が複数のジャンルに紐づいている(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(3)->create();

        $book->genres()->attach($genres->pluck('id'));

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }

        $this->assertCount(3, $book->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());
    }

    /** @test */
    public function １つの書籍から紐づく複数のレビューが取得できる(): void
    {
        $users = User::factory()->count(3)->create();
        $book = Book::factory()->create();

        foreach ($users as $user) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $this->assertCount(3, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    
    /** @test */
    public function 中間テーブルを介して、１つの書籍が複数のユーザーに紐づいている(): void
    {
        $users = User::factory()->count(3)->create();
        $book = Book::factory()->create();

        $book->favoritedByUser()->attach($users->pluck('id'));

        foreach ($users as $user) {
            $this->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
        }

        $this->assertCount(3, $book->favoritedByUser);
        $this->assertInstanceOf(User::class, $book->favoritedByUser->first());
    }
}
