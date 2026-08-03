<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookFavoriteFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_未認証の状態でにお気に入り一覧ページにアクセスした場合ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('favorites.index'));

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_認証済みの状態でにお気に入り一覧ページが正しく表示される()
    {
        $user = User::first();
        $books = Book::all();

        foreach ($books as $book) {
            $user->favoriteBooks()->syncWithoutDetaching([$book->id]);
        }

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee($books->first()->title);
        $response->assertViewHas('books', function ($paginator) {
            return $paginator->perPage() === 10
                && $paginator->total() === 11;
        });
    }

    public function test_書籍をお気に入りに登録できる()
    {
        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->detach($book->id);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));

        $this->assertDatabaseHas('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
    }

    public function test_書籍をお気に入りに登録するとアイコンの色が変化する()
    {
        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->detach($book->id);

        $response = $this->actingAs($user)->get(route('books.show', $book));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertRedirect();

        $showResponse = $this->actingAs($user)->get(route('books.show', $book));
        $showResponse->assertSee('text-red-500');
    }

    public function test_お気に入りを解除できる()
    {
        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->syncWithoutDetaching([$book->id]);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
    }

    public function test_お気に入りを解除するとアイコンの色が元に戻る()
    {
        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->syncWithoutDetaching([$book->id]);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertRedirect();

        $showResponse = $this->actingAs($user)->get(route('books.show', $book));
        $showResponse->assertSee('text-gray-400');
    }
}
