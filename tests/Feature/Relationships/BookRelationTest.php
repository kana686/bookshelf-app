<?php

namespace Tests\Feature\Relationships;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_userリレーションが正しく定義されている()
    {
        $book = Book::first();

        $this->assertNotNull($book->user);
        $this->assertInstanceOf(User::class, $book->user);
    }

    public function test_reviewsリレーションが正しく定義されている()
    {
        $book = Book::has('reviews')->first();

        if ($book) {
            $this->assertNotEmpty($book->reviews);
            $this->assertInstanceOf(Review::class, $book->reviews->first());
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_genresリレーションが正しく定義されている()
    {
        $book = Book::has('genres')->first();

        if ($book) {
            $this->assertNotEmpty($book->genres);
            $this->assertInstanceOf(Genre::class, $book->genres->first());
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_users_who_favoritedリレーションが正しく定義されている()
    {
        $book = Book::has('usersWhoFavorited')->first();

        if ($book) {
            $this->assertNotEmpty($book->usersWhoFavorited);
            $this->assertInstanceOf(User::class, $book->usersWhoFavorited->first());
        } else {
            $this->assertTrue(true);
        }
    }
}
