<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_ゲストがのbooks画面にアクセスし10件表示されること()
    {
        $response = $this->get('/books');
        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            $firstBook = $books->first();

            if (! $firstBook) {
                return false;
            }

            return $books->count() === 10
                && isset($firstBook->title)
                && isset($firstBook->author)
                && $firstBook->relationLoaded('genres')
                && $firstBook->genres->isNotEmpty();
        });

        $firstBookModel = Book::with('genres')->first();
        if ($firstBookModel) {
            $response->assertSee($firstBookModel->title);
            $response->assertSee($firstBookModel->author);
            if ($firstBookModel->genres->isNotEmpty()) {
                $response->assertSee($firstBookModel->genres->first()->name);
            }
        }
    }

    public function test_ゲストがルート_ur_lにアクセスし10件表示されること()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            $firstBook = $books->first();

            if (! $firstBook) {
                return false;
            }

            return $books->count() === 10
                && isset($firstBook->title)
                && isset($firstBook->author)
                && $firstBook->relationLoaded('genres')
                && $firstBook->genres->isNotEmpty();
        });

        $firstBookModel = Book::with('genres')->first();
        if ($firstBookModel) {
            $response->assertSee($firstBookModel->title);
            $response->assertSee($firstBookModel->author);
            if ($firstBookModel->genres->isNotEmpty()) {
                $response->assertSee($firstBookModel->genres->first()->name);
            }
        }
    }

    public function test_ログインユーザーがbooks画面にアクセスし10件表示されること()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/books');
        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            $firstBook = $books->first();

            if (! $firstBook) {
                return false;
            }

            return $books->count() === 10
                && isset($firstBook->title)
                && isset($firstBook->author)
                && $firstBook->relationLoaded('genres')
                && $firstBook->genres->isNotEmpty();
        });

        $firstBookModel = Book::with('genres')->first();
        if ($firstBookModel) {
            $response->assertSee($firstBookModel->title);
            $response->assertSee($firstBookModel->author);
            if ($firstBookModel->genres->isNotEmpty()) {
                $response->assertSee($firstBookModel->genres->first()->name);
            }
        }
    }

    public function test_ログインユーザーがルートurlにアクセスし10件表示されること()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            $firstBook = $books->first();

            if (! $firstBook) {
                return false;
            }

            return $books->count() === 10
                && isset($firstBook->title)
                && isset($firstBook->author)
                && $firstBook->relationLoaded('genres')
                && $firstBook->genres->isNotEmpty();
        });

        $firstBookModel = Book::with('genres')->first();
        if ($firstBookModel) {
            $response->assertSee($firstBookModel->title);
            $response->assertSee($firstBookModel->author);
            if ($firstBookModel->genres->isNotEmpty()) {
                $response->assertSee($firstBookModel->genres->first()->name);
            }
        }
    }

    public function test_ページネーションリンクが機能し2ページ目に遷移できること()
    {
        $response = $this->get('/?page=2');
        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            return $books->count() > 0;
        });
    }

    public function test_書籍詳細画面が正しく表示されること()
    {
        $book = Book::with(['genres', 'reviews.user'])->first();

        $this->assertNotNull($book, '検証用の書籍データが存在しません');

        $response = $this->get("/books/{$book->id}");
        $response->assertStatus(200);

        $response->assertViewHas('book', function ($viewBook) use ($book) {
            return $viewBook->id === $book->id;
        });

        $response->assertSee($book->title);
        $response->assertSee($book->author);
        $response->assertSee($book->isbn);

        if ($book->genres->isNotEmpty()) {
            $response->assertSee($book->genres->first()->name);
        }
        if ($book->reviews->isNotEmpty()) {
            foreach ($book->reviews as $review) {
                if ($review->comment) {
                    $response->assertSee($review->comment);
                }
                if ($review->user) {
                    $response->assertSee($review->user->name);
                }
            }
        } else {
            $response->assertSee('まだレビューはありません。');
        }
    }

    // 書籍CRUDテスト
    public function test_ログインユーザーが書籍登録画面を表示できる()
    {
        $user = User::first();

        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);

        $response->assertSee('書籍登録');
    }

    public function test_未認証の状態で、書籍登録画面にアクセスした場合、ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('books.create'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');

    }

    public function test_必須項目を全て入力して正常に書籍登録ができる()
    {
        $user = User::first();

        $genre = Genre::first();

        $bookData = [
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'genres' => [$genre ? $genre->id : 1],
        ];

        $response = $this->actingAs($user)->post(route('books.store'), $bookData);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890',
            'user_id' => $user->id,
        ]);

        $book = Book::where('title', 'テスト登録書籍')->latest()->first();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました');
    }

    // 登録時バリデーション
    #[DataProvider('invalidStoreDataProvider')]
    public function test_登録時バリデーションエラー($field, $invalidValue, $bookDataOverrides, $expectedOriginal, $expectedMessage)
    {
        $user = User::first();
        $genre = Genre::first();

        $bookData = array_merge([
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'genres' => [$genre ? $genre->id : 1],
        ], $bookDataOverrides);

        $response = $this->actingAs($user)->post(route('books.store'), $bookData);

        $response->assertInvalid([
            $field => $expectedMessage,
        ]);

        $this->assertDatabaseMissing('books', [
            'author' => 'テスト著者名',
        ]);
    }

    public static function invalidStoreDataProvider()
    {
        return [
            'タイトル未入力' => ['title', '', ['title' => ''], ['title' => '元のタイトル'], 'タイトルを入力してください'],
            '著者名未入力' => ['author', '', ['author' => ''], ['author' => 'テスト著者名'], '著者名を入力してください'],
            'ISBN未入力' => ['isbn', '', ['isbn' => ''], ['isbn' => '1234567890'], 'ISBNを入力してください'],
            'ISBN 11桁' => ['isbn', '12345678901', ['isbn' => '12345678901'], ['isbn' => '1234567890'], 'ISBNは10桁または13桁で入力してください'],
            'ISBN 14桁' => ['isbn', '12345678901234', ['isbn' => '12345678901234'], ['isbn' => '1234567890'], 'ISBNは10桁または13桁で入力してください'],
            '出版日未入力' => ['published_date', '', ['published_date' => ''], ['published_date' => '2026-06-01'], '出版日を入力してください'],
            'ジャンル未選択' => ['genres', [], ['genres' => []], ['title' => '元のタイトル'], 'ジャンルを1つ以上選択してください'],
        ];
    }

    // 編集画面
    public function test_自分が登録した書籍の編集画面が正しく表示される()
    {
        $user = User::first();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '編集テスト用書籍',
            'author' => 'テスト著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $user->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertSee($book->author);
    }

    public function test_自分が登録した書籍を正常に編集更新できる()
    {
        $user = User::first();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $user->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $updateData = [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-02',
            'genres' => [$genre ? $genre->id : 1],
        ];

        $response = $this->actingAs($user)->put(route('books.update', $book), $updateData);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'published_date' => '2026-06-02',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍情報を更新しました');
    }

    public function test_他人の登録した書籍の編集画面にアクセスした場合エラーになる()
    {
        $user = User::first();

        $otherUser = User::factory()->create();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '他人の書籍',
            'author' => '他人著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $otherUser->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        $response->assertForbidden();
    }

    // 更新時バリデーションエラー
    #[DataProvider('invalidUpdateDataProvider')]
    public function test_更新時バリデーションエラー($field, $invalidValue, $bookDataOverrides, $expectedOriginal, $expectedMessage)
    {
        $user = User::first();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '元のタイトル',
            'author' => 'テスト著者名',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $user->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $bookData = array_merge([
            'title' => 'テスト登録書籍',
            'author' => 'テスト著者名',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'genres' => [$genre ? $genre->id : 1],
        ], $bookDataOverrides);

        $response = $this->actingAs($user)->put(route('books.update', $book), $bookData);

        $response->assertInvalid([
            $field => $expectedMessage,
        ]);

        $this->assertDatabaseHas('books', array_merge([
            'id' => $book->id,
        ], $expectedOriginal));

        if ($genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public static function invalidUpdateDataProvider()
    {
        return [
            'タイトル未入力' => ['title', '', ['title' => ''], ['title' => '元のタイトル'], 'タイトルを入力してください'],
            '著者名未入力' => ['author', '', ['author' => ''], ['author' => 'テスト著者名'], '著者名を入力してください'],
            'ISBN未入力' => ['isbn', '', ['isbn' => ''], ['isbn' => '1234567890'], 'ISBNを入力してください'],
            'ISBN 11桁' => ['isbn', '12345678901', ['isbn' => '12345678901'], ['isbn' => '1234567890'], 'ISBNは10桁または13桁で入力してください'],
            'ISBN 14桁' => ['isbn', '12345678901234', ['isbn' => '12345678901234'], ['isbn' => '1234567890'], 'ISBNは10桁または13桁で入力してください'],
            '出版日未入力' => ['published_date', '', ['published_date' => ''], ['published_date' => '2026-06-01'], '出版日を入力してください'],
            'ジャンル未選択' => ['genres', [], ['genres' => []], ['title' => '元のタイトル'], 'ジャンルを1つ以上選択してください'],
        ];
    }

    // 削除
    public function test_自分が登録した書籍を正常に削除できる()
    {
        $user = User::first();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '削除テスト用書籍',
            'author' => '削除テスト著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $user->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');
    }

    public function test_他人が登録した書籍を削除しようとした場合エラーになる()
    {
        $user = User::first();

        $otherUser = User::factory()->create();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '他人の削除テスト用書籍',
            'author' => '他人著者',
            'isbn' => '1234567890',
            'published_date' => '2026-06-01',
            'user_id' => $otherUser->id,
        ]);

        if ($genre) {
            $book->genres()->attach($genre->id);
        }

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
