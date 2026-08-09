<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_必須項目と正しい値を入力して正常にレビューが投稿できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $reviewData = [
            'rating' => 5,
            'comment' => 'とても参考になる素晴らしい書籍です。',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'とても参考になる素晴らしい書籍です。',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました');
    }

    public function test_評価が未入力の場合バリデーションエラーになる()
    {
        $user = User::first();
        $book = Book::first();

        $reviewData = [
            'rating' => '',
            'comment' => 'コメントのみ入力',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => 'コメントのみ入力',
        ]);
    }

    public function test_評価に1から5以外の数値を入れた場合バリデーションエラーになる()
    {
        $user = User::first();
        $book = Book::first();

        $reviewData = [
            'rating' => 6,
            'comment' => '不正な評価値テスト',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '不正な評価値テスト',
        ]);
    }

    public function test_コメントが未入力の場合バリデーションエラーになる()
    {
        $user = User::first();
        $book = Book::first();

        $reviewData = [
            'rating' => 4,
            'comment' => '',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '',
        ]);
    }

    public function test_コメントが256文字以上の場合バリデーションエラーメッセージが表示される()
    {
        $user = User::first();
        $book = Book::first();

        $reviewData = [
            'rating' => 4,
            'comment' => str_repeat('あ', 256),
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => str_repeat('あ', 256),
        ]);
    }

    public function test_同じ書籍に対してすでにレビューを投稿している場合バリデーションエラーになる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => '最初のレビュー',
        ]);

        $reviewData = [
            'rating' => 4,
            'comment' => '二回目のレビュー',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertSessionHasErrors('book_id');

        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '二回目のレビュー',
        ]);
    }

    public function test_自分が投稿したレビューを正常に編集更新できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 3,
            'comment' => '更新前コメント',
        ]);

        $editResponse = $this->actingAs($user)->get(route('reviews.edit', $review));
        $editResponse->assertStatus(200);

        $editResponse->assertSee($review->comment);
        $editResponse->assertSee($review->rating);

        $updateData = [
            'rating' => 5,
            'comment' => '更新後コメント',
        ];

        $response = $this->actingAs($user)->put(route('reviews.update', $review), $updateData);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを更新しました');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後コメント',
        ]);
    }

    public function test_他人が投稿したレビューの編集画面にアクセスした場合エラーになる()
    {
        $user = User::factory()->create();
        $otherUser = User::where('id', '!=', $user->id)->first();
        $book = Book::factory()->create();

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id,
            'rating' => 4,
            'comment' => '他人のレビュー',
        ]);

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_自分が投稿したレビューを正常に削除できる()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => '削除用コメント',
        ]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを削除しました');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_他人が投稿したレビューを削除しようとした場合エラーになる()
    {
        $user = User::factory()->create();
        $otherUser = User::where('id', '!=', $user->id)->first();
        $book = Book::factory()->create();

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id,
            'rating' => 4,
            'comment' => '他人の削除用コメント',
        ]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
