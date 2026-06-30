<?php

namespace Tests\Feature\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class ISBNCodeSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function GoogleBooksApiから書籍情報を取得し_jsonが正しく返る(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'コンテナ物語',
                            'authors' => ['マルク・レビンソン'],
                            'publishedDate' => '2020-01-01',
                            'description' => 'test',
                            'imageLinks' => [
                                'thumbnail' => 'http://example.com/image.jpg'
                            ],
                            'categories' => ['経済']
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/books/isbn/9784167906055');

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'published_date' => '2020-01-01',
            'description' => 'test',
            'image_url' => 'http://example.com/image.jpg',
        ]);
    }

    /** @test */
    public function 存在しないISBNコードを入力した場合エラーjsonが返る(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => []
            ], 200)
        ]);

        $response = $this->getJson('/books/isbn/9999999999999');

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }
}
