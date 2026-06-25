<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つの読書計画が特定のユーザーに紐づく(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $readingPlan->user);
        $this->assertEquals($user->id, $readingPlan->user->id);
    }
    
    /** @test */
    public function １つの読書計画が特定の本に紐づく(): void
    {
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $readingPlan->book);
        $this->assertEquals($book->id, $readingPlan->book->id);
    }
}
