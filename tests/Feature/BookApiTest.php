<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_条件を指定して書籍一覧が正しく取得できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book1 = Book::factory()->create([
            'title' => 'Laravelの教科書',
            'user_id' => $user->id,
        ]);
        $book1->genres()->attach($genre->id);

        $book2 = Book::factory()->create([
            'title' => 'PHP入門',
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/v1/books?keyword=Laravel&genre_id={$genre->id}&per_page=10");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Laravelの教科書']);
    }

    public function test_書籍一覧がデフォルトまたは指定件数ずつ正しく取得できる()
    {
        $user = User::factory()->create();

        Book::factory()->count(25)->create([
            'user_id' => $user->id,
        ]);

        $responseDefault = $this->getJson('/api/v1/books');
        $responseDefault->assertStatus(200)
            ->assertJsonCount(20, 'data');

        $responseCustom = $this->getJson('/api/v1/books?per_page=15');
        $responseCustom->assertStatus(200)
            ->assertJsonCount(15, 'data');
    }

    #[DataProvider('invalidPaginationDataProvider')]
    public function test_不正なパラメータを指定した際にバリデーションエラーとなる($perPage, $expectedMessage)
    {
        $response = $this->getJson("/api/v1/books?per_page={$perPage}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page'])
            ->assertJson([
                'errors' => [
                    'per_page' => [
                        $expectedMessage,
                    ],
                ],
            ]);
    }

    public static function invalidPaginationDataProvider()
    {
        return [
            '0を指定' => [0, '1ページあたりの件数は1以上の整数で指定してください'],
            '51を指定（上限オーバー）' => [51, '1ページあたりの件数は50以下の整数で指定してください'],
            '文字列を指定' => ['abc', '1ページあたりの件数は1以上の整数で指定してください'],
        ];
    }

    public function test_指定した_i_dの書籍詳細が正しく取得できる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $book->id,
                'title' => $book->title,
            ]);
    }

    public function test_存在しない書籍_i_dを指定した際にエラーレスポンスが返る()
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => '指定された書籍が見つかりません。',
            ]);
    }
}
