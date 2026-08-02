<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_他人のレビューに対していいねができる()
    {
        $user = User::first();

        $review = Review::where('user_id', '!=', $user->id)->first();

        $this->assertNotNull($review, '検証用の他人レビューが存在しません');

        $response = $this->actingAs($user)->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);

        $response->assertBack();
    }

    public function test_いいねを登録するとアイコンの色が変化すること()
    {
        $user = User::first();
        $review = Review::where('user_id', '!=', $user->id)->first();

        $this->assertNotNull($review, '検証用の他人レビューが存在しません');

        $response = $this->actingAs($user)->get(route('books.show', $review->book));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));
        $response->assertBack();

        $showResponse = $this->actingAs($user)->get(route('books.show', $review->book));
        $showResponse->assertSee('text-blue-500');
    }

    public function test_いいねを解除できる()
    {
        $user = User::first();
        $review = Review::where('user_id', '!=', $user->id)->first();

        $this->assertNotNull($review, '検証用の他人レビューが存在しません');

        $review->likes()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);

        $response->assertBack();
    }

    public function test_いいねを解除するとアイコンの色が元に戻ること()
    {
        $user = User::first();
        $review = Review::where('user_id', '!=', $user->id)->first();

        $this->assertNotNull($review, '検証用の他人レビューが存在しません');

        $review->likes()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));
        $response->assertBack();

        $showResponse = $this->actingAs($user)->get(route('books.show', $review->book));
        $showResponse->assertSee('text-gray-500');
    }
}
