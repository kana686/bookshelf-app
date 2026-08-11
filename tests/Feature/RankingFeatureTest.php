<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createBookWithReview(User $user, int $rating): Book
    {
        $book = Book::factory()->create(['user_id' => $user->id]);
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => $rating,
        ]);

        return $book;
    }

    public function test_未ログインの状態でもランキング画面にアクセスできる()
    {
        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
    }

    public function test_ログイン状態でもランキング画面にアクセスできる()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ranking.index'));

        $response->assertStatus(200);
    }

    public function test_レビュー平均評価の高い_to_p10の書籍が降順で並び順通りに表示される()
    {
        $user = User::factory()->create();

        $book1 = $this->createBookWithReview($user, 3);
        $book2 = $this->createBookWithReview($user, 5);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($book1, $book2) {
            return $rankedBooks->first()->id === $book2->id
                && $rankedBooks->skip(1)->first()->id === $book1->id;
        });
    }

    public function test_レビューがない書籍はランキングから除外される()
    {
        $user = User::factory()->create();

        $bookWithReview = $this->createBookWithReview($user, 4);
        $bookWithoutReview = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertSee($bookWithReview->title);
        $response->assertDontSee($bookWithoutReview->title);
    }

    public function test_書籍総数が10件未満の場合でも正しく表示される()
    {
        $user = User::factory()->create();

        $book1 = $this->createBookWithReview($user, 3);
        $book2 = $this->createBookWithReview($user, 5);
        $book3 = $this->createBookWithReview($user, 4);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($book1, $book2, $book3) {
            return $rankedBooks->count() === 3
                && $rankedBooks->first()->id === $book2->id
                && $rankedBooks->skip(1)->first()->id === $book3->id
                && $rankedBooks->last()->id === $book1->id;
        });
    }

    public function test_同じレビュー平均評価を持つ書籍が複数ある場合でも正しく表示される()
    {
        $user = User::factory()->create();

        $book1 = $this->createBookWithReview($user, 4);
        $book2 = $this->createBookWithReview($user, 4);

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($book1, $book2) {
            return $rankedBooks->first()->id === $book1->id
                && $rankedBooks->skip(1)->first()->id === $book2->id;
        });
    }
}
