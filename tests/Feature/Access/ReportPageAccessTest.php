<?php

namespace Tests\Feature\Access;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;

class ReportPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証時ログイン画面へリダイレクトする(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 総レビュー数、読了冊数、平均評価点が表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(5)->create();

        $reviews = Review::factory()->count(5)->create(['user_id' => $user->id]);

        foreach ($books as $book) {
            ReadingPlan::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'completed_at' => now(),
                'status' => 'completed',
            ]);
        }

        $readingPlans = ReadingPlan::all();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertViewHas('stats', function ($stats) use ($reviews, $readingPlans) {
            return
                $stats['summary']['total_reviews'] === 5 &&
                $stats['summary']['books_read'] === $readingPlans->count() &&
                $stats['summary']['average_rating'] === $reviews->avg('rating');
        });
    }

    /** @test */
    public function 評価分布において星1〜5ごとの評価件数が表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(5)->create();

        Review::factory()->create(['user_id' => $user->id, 'rating' => 1]);
        Review::factory()->count(2)->create(['user_id' => $user->id, 'rating' => 2]);
        Review::factory()->count(3)->create(['user_id' => $user->id, 'rating' => 3]);
        Review::factory()->count(4)->create(['user_id' => $user->id, 'rating' => 4]);
        Review::factory()->count(5)->create(['user_id' => $user->id, 'rating' => 5]);

        $expected = [1, 2, 3, 4, 5];

        $response = $this->actingAs($user)->get('/reports');

        $response->assertViewHas('stats', function ($stats) use ($expected) {
            return $stats['rating_distribution']->toArray() === $expected;
        });
    }
    
    /** @test */
    public function 星４以上の書籍を評価の高い順に最大５件表示する(): void
    {
        $user = User::factory()->create();
        
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();
        $book4 = Book::factory()->create();
        $book5 = Book::factory()->create();
        $book6 = Book::factory()->create();

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book4->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book5->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book6->id, 'rating' => 3]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
            $book3->title,
            $book4->title,
            $book5->title,
        ]);
    }

    /** @test */
    public function ジャンルごとの平均評価と件数を高い順に最大5件表示する(): void
    {
        $user = User::factory()->create();
        
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();
        $book4 = Book::factory()->create();
        $book5 = Book::factory()->create();
        $book6 = Book::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $genre3 = Genre::factory()->create();
        $genre4 = Genre::factory()->create();
        $genre5 = Genre::factory()->create();
        $genre6 = Genre::factory()->create();

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);
        $book3->genres()->attach($genre3->id);
        $book4->genres()->attach($genre4->id);
        $book5->genres()->attach($genre5->id);
        $book6->genres()->attach($genre6->id);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book3->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book4->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book5->id, 'rating' => 2]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book6->id, 'rating' => 1]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertSeeInOrder([
            $genre1->name,
            $genre2->name,
            $genre3->name,
            $genre4->name,
            $genre5->name,
        ]);
    }
}