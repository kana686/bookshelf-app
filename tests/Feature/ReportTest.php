<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーがアクセスした際4種の統計情報が正しく表示される()
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create(['user_id' => $user->id, 'title' => '高評価の本']);
        $book2 = Book::factory()->create(['user_id' => $user->id, 'title' => 'やや高評価の本']);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) use ($book1, $book2) {
            if ($stats['summary']['total_reviews'] !== 2) {
                return false;
            }

            $topBooks = $stats['top_rated_books'];
            if (count($topBooks) !== 2) {
                return false;
            }

            return $topBooks[0]['id'] === $book2->id && $topBooks[1]['id'] === $book1->id;
        });
    }

    public function test_未認証ユーザーがアクセスした際ログイン画面にリダイレクトされる()
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
