<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_未認証の状態でジャンル一覧画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_認証済みの状態でジャンル一覧画面にアクセスした場合各ジャンルに紐づく書籍数付きでジャンル一覧が表示される()
    {
        $user = User::first();
        $genre = Genre::first();
        $bookCount = $genre->books()->count();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee($genre->name);
        $response->assertSee((string) $bookCount);
    }

    public function test_ジャンル一覧画面でジャンルが10件ページでページネーション表示される()
    {
        $user = User::first();

        Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);

        $response->assertViewHas('genres', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 12;
        });
    }

    public function test_未認証の状態でジャンル詳細画面にアクセスした場合ログイン画面にリダイレクトされる()
    {
        $genre = Genre::first() ?? Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_ジャンル詳細画面で紐づく書籍が10件ページでページネーション表示される()
    {
        $user = User::first();
        $genre = Genre::first();

        $currentCount = $genre->books()->count();
        if ($currentCount < 12) {
            $neededCount = 12 - $currentCount;

            $books = Book::factory()->count($neededCount)->create([
                'user_id' => $user->id,
            ]);

            $genre->books()->attach($books->pluck('id'));
        }

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee($genre->name);

        $response->assertViewHas('books', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 12;
        });
    }
}
