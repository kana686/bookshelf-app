<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_キーワードを入力して検索した際部分一致する書籍のみが表示される()
    {
        $user = User::factory()->create();

        $matchedBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門ガイド',
            'author' => 'テスト太郎',
        ]);

        $unmatchedBook = Book::factory()->create([
            'title' => 'Vue.jsの教科書',
            'author' => 'サンプル次郎',
        ]);

        $response = $this->get('/books?keyword=Laravel');

        $response->assertStatus(200);

        $response->assertSee($matchedBook->title);

        $response->assertDontSee($unmatchedBook->title);
    }

    public function test_ジャンルを選択して絞り込んだ際選択したジャンルに紐づく書籍のみが表示される()
    {
        $user = User::factory()->create();

        $targetGenre = Genre::create(['name' => 'テスト対象ジャンル']);
        $otherGenre = Genre::create(['name' => 'テスト除外ジャンル']);

        $targetBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '【表示されるべき】ターゲット書籍',
        ]);
        $targetBook->genres()->attach($targetGenre->id);

        $otherBook = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '【表示されてはいけない】無関係の書籍',
        ]);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->get("/books?genre={$targetGenre->id}");

        $response->assertStatus(200);

        $response->assertSee($targetBook->title);

        $response->assertDontSee($otherBook->title);
    }

    public function test_ソート順を変更した際それぞれ指定された順序で表示される()
    {
        $this->seed(DatabaseSeeder::class);

        $responseNewest = $this->get('/books?sort=newest');
        $responseNewest->assertStatus(200);

        $responseOldest = $this->get('/books?sort=oldest');
        $responseOldest->assertStatus(200);

        $responseTitle = $this->get('/books?sort=title');
        $responseTitle->assertStatus(200);

        $responseRating = $this->get('/books?sort=rating');
        $responseRating->assertStatus(200);
    }

    public function test_検索フィルタソートを行った状態でページネーションのリンクを押した際条件を維持したまま指定ページに遷移する()
    {
        $user = User::factory()->create();

        Book::factory()->count(12)->create([
            'user_id' => $user->id,
            'title' => 'テスト用書籍',
        ]);

        $response = $this->get('/books?keyword=テスト&page=2');

        $response->assertStatus(200);

        $response->assertViewHas('books', function ($books) {
            return $books instanceof LengthAwarePaginator
                && $books->currentPage() === 2
                && $books->count() === 2;
        });
    }
}
