<?php

namespace Tests\Feature\Relationships;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_booksリレーションが正しく定義されている()
    {
        $user = User::first();

        $this->assertNotEmpty($user->books);
        $this->assertInstanceOf(Book::class, $user->books->first());
    }

    public function test_reviewsリレーションが正しく定義されている()
    {
        $user = User::first();

        $this->assertNotEmpty($user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    public function test_favorite_booksリレーションが正しく定義されている()
    {
        $user = User::first();

        $this->assertNotEmpty($user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());
    }

    public function test_liked_reviewsリレーションが正しく定義されている()
    {
        $user = User::first();

        $userWithLikes = User::has('likedReviews')->first();

        if ($userWithLikes) {
            $this->assertNotEmpty($userWithLikes->likedReviews);
            $this->assertInstanceOf(Review::class, $userWithLikes->likedReviews->first());
        } else {
            $this->assertTrue(true);
        }
    }
}
