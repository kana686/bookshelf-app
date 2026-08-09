<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    // 登録時バリデーションエラー
    #[DataProvider('invalidReviewStoreDataProvider')]
    public function test_レビュー登録時バリデーションエラー($field, $reviewDataOverrides, $expectedMessage)
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $reviewData = array_merge([
            'rating' => 4,
            'comment' => 'テスト用コメント',
        ], $reviewDataOverrides);

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $reviewData);

        $response->assertInvalid([
            $field => $expectedMessage,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => $reviewDataOverrides['comment'] ?? 'テスト用コメント',
        ]);
    }

    public static function invalidReviewStoreDataProvider()
    {
        return [
            '評価未入力' => [
                'rating',
                ['rating' => ''],
                '評価を選択してください',
            ],
            '評価が6（範囲外）' => [
                'rating',
                ['rating' => 6],
                '評価は1から5の間で選択してください',
            ],
            'コメント未入力' => [
                'comment',
                ['comment' => ''],
                'コメントを入力してください',
            ],
            'コメントが256文字以上' => [
                'comment',
                ['comment' => str_repeat('あ', 256)],
                'コメントは255文字以内で入力してください',
            ],
        ];
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

    // 編集
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

    // 編集時バリデーションエラー
    #[DataProvider('invalidReviewUpdateDataProvider')]
    public function test_レビュー編集時バリデーションエラー($field, $reviewDataOverrides, $expectedMessage)
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => '元のコメント',
        ]);

        $reviewData = array_merge([
            'rating' => 5,
            'comment' => '更新後のテストコメント',
        ], $reviewDataOverrides);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), $reviewData);

        $response->assertInvalid([
            $field => $expectedMessage,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '元のコメント',
        ]);
    }

    public static function invalidReviewUpdateDataProvider()
    {
        return [
            '評価未入力' => [
                'rating',
                ['rating' => ''],
                '評価を選択してください',
            ],
            '評価が6（範囲外）' => [
                'rating',
                ['rating' => 6],
                '評価は1から5の間で選択してください',
            ],
            'コメント未入力' => [
                'comment',
                ['comment' => ''],
                'コメントを入力してください',
            ],
            'コメントが256文字以上' => [
                'comment',
                ['comment' => str_repeat('あ', 256)],
                'コメントは255文字以内で入力してください',
            ],
        ];
    }

    // 削除
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
