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

    public function test_検索条件を維持したままページを移動できる()
    {
        $user = User::factory()->create();

        Book::factory()->count(12)->create([
            'user_id' => $user->id,
            'title' => 'Laravelの教科書',
        ]);

        Book::factory()->count(3)->create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel&page=2&per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['title' => 'Laravelの教科書']);
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

    public function test_指定したidの書籍詳細が正しく取得できる()
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

    public function test_存在しない書籍idを指定した際にエラーレスポンスが返る()
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => '指定された書籍が見つかりません。',
            ]);
    }

    // 登録
    public function test_正しい入力値で書籍情報を新規登録しジャンルを紐付けられる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $bookData = [
            'user_id' => $user->id,
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'テスト登録書籍']);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト登録書籍',
            'isbn' => '1234567890123',
            'user_id' => $user->id,
        ]);
    }

    #[DataProvider('invalidStoreDataProvider')]
    public function test_登録時バリデーションエラー($field, $bookDataOverrides, $expectedMessage)
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $baseData = [
            'user_id' => $user->id,
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-06-01',
            'genres' => [$genre->id],
        ];

        $bookData = array_merge($baseData, $bookDataOverrides);

        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field])
            ->assertJsonPath("errors.{$field}.0", $expectedMessage);
    }

    public static function invalidStoreDataProvider()
    {
        return [
            'タイトル未入力' => [
                'title',
                ['title' => ''],
                'タイトルを入力してください',
            ],
            'タイトル256文字以上' => [
                'title',
                ['title' => str_repeat('あ', 256)],
                'タイトルは255文字以内で入力してください',
            ],
            '著者名未入力' => [
                'author',
                ['author' => ''],
                '著者名を入力してください',
            ],
            '著者名256文字以上' => [
                'author',
                ['author' => str_repeat('あ', 256)],
                '著者名は255文字以内で入力してください',
            ],
            'ISBN未入力' => [
                'isbn',
                ['isbn' => ''],
                'ISBNを入力してください',
            ],
            'ISBN 11桁' => [
                'isbn',
                ['isbn' => '12345678901'],
                'ISBNは10桁または13桁で入力してください',
            ],
            'ISBN 14桁' => [
                'isbn',
                ['isbn' => '12345678901234'],
                'ISBNは10桁または13桁で入力してください',
            ],
            '出版日未入力' => [
                'published_date',
                ['published_date' => ''],
                '出版日を入力してください',
            ],
            '説明256文字以上' => [
                'description',
                ['description' => str_repeat('あ', 256)],
                '説明は255文字以内で入力してください',
            ],
            'ジャンル未選択' => [
                'genres',
                ['genres' => []],
                'ジャンルを1つ以上選択してください',
            ],
        ];
    }

    public function test_isbnが重複している場合、バリデーションエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        Book::factory()->create([
            'isbn' => '9784101010014',
            'user_id' => $user->id,
        ]);
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $bookData = [
            'user_id' => $user->id,
            'title' => '重複テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784101010014',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn'])
            ->assertJsonPath('errors.isbn.0', 'このISBNは既に登録されています');
    }

    // 更新
    public function test_指定したidの書籍情報を更新しジャンルを再紐付けできる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'user_id' => $user->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9784101010025',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => '更新後のタイトル']);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    public function test_存在しない書籍idを指定して更新を行った場合にエラーレスポンスが返る()
    {
        $user = User::factory()->create();
        $updateData = [
            'user_id' => $user->id,
            'title' => '存在しない更新',
            'author' => '著者',
            'isbn' => '9784101010032',
            'published_date' => '2026-01-01',
            'genres' => [1],
        ];

        $response = $this->putJson('/api/v1/books/99999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'error' => '指定された書籍が見つかりません。',
            ]);
    }

    #[DataProvider('invalidUpdateDataProvider')]
    public function test_更新時バリデーションエラー($field, $bookDataOverrides, $expectedMessage)
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $baseData = [
            'user_id' => $user->id,
            'title' => '更新タイトル',
            'author' => '更新著者名',
            'isbn' => '9784101010099',
            'published_date' => '2026-06-01',
            'genres' => [$genre->id],
        ];

        $bookData = array_merge($baseData, $bookDataOverrides);

        $response = $this->putJson("/api/v1/books/{$book->id}", $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field])
            ->assertJsonPath("errors.{$field}.0", $expectedMessage);
    }

    public static function invalidUpdateDataProvider()
    {
        return [
            'タイトル未入力' => [
                'title',
                ['title' => ''],
                'タイトルを入力してください',
            ],
            'タイトル256文字以上' => [
                'title',
                ['title' => str_repeat('あ', 256)],
                'タイトルは255文字以内で入力してください',
            ],
            '著者名未入力' => [
                'author',
                ['author' => ''],
                '著者名を入力してください',
            ],
            '著者名256文字以上' => [
                'author',
                ['author' => str_repeat('あ', 256)],
                '著者名は255文字以内で入力してください',
            ],
            'ISBN未入力' => [
                'isbn',
                ['isbn' => ''],
                'ISBNを入力してください',
            ],
            'ISBN 11桁' => [
                'isbn',
                ['isbn' => '12345678901'],
                'ISBNは10桁または13桁で入力してください',
            ],
            '出版日未入力' => [
                'published_date',
                ['published_date' => ''],
                '出版日を入力してください',
            ],
            'ジャンル未選択' => [
                'genres',
                ['genres' => []],
                'ジャンルを1つ以上選択してください',
            ],
        ];
    }

    public function test_既に登録されている他の書籍のisbnを指定した場合にバリデーションエラーとなる()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create(['isbn' => '9784101010099']);

        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '1234567890123']);

        $updateData = [
            'user_id' => $user->id,
            'title' => '更新タイトル',
            'author' => '更新著者名',
            'isbn' => '9784101010099',
            'published_date' => '2026-06-01',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['isbn'])
            ->assertJsonPath('errors.isbn.0', 'このISBNは既に登録されています');
    }

    public function test_自分自身のisbnを指定して更新する場合はバリデーションエラーにならない()
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '1234567890123']);

        $updateData = [
            'user_id' => $user->id,
            'title' => 'タイトルだけ変更',
            'author' => '更新著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-06-01',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'タイトルだけ変更']);
    }

    // 削除
    public function test_指定したidの書籍情報を削除し関連データが適切に処理される()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_存在しない書籍idを指定して削除を行った場合にエラーレスポンスが返る()
    {
        $response = $this->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => '指定された書籍が見つかりません。',
            ]);
    }
}
