<?php

namespace Tests\Feature\Relationships;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_userリレーションが正しく定義されている(): void
    {
        $review = Review::with('user')->first();

        $this->assertNotNull($review);
        $this->assertInstanceOf(User::class, $review->user);
    }

    public function test_bookリレーションが正しく定義されている(): void
    {
        $review = Review::with('book')->first();

        $this->assertNotNull($review);
        $this->assertInstanceOf(Book::class, $review->book);
    }

    public function test_users_who_likedリレーションが正しく定義されている(): void
    {
        $review = Review::has('usersWhoLiked')->first();

        if (! $review) {
            $review = Review::first();
            $user = User::first();
            $review->usersWholiked()->attach($user->id);
        }

        $this->assertNotEmpty($review->usersWhoLiked);
        $this->assertInstanceOf(User::class, $review->usersWhoLiked->first());
    }
}
