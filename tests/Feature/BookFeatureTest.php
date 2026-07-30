<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_ゲストがbooks画面にアクセスし10件表示されること()
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

    public function test_ログインユーザーがルート_ur_lにアクセスし10件表示されること()
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
        $book = Book::with('genres')->first();

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
    }
}
