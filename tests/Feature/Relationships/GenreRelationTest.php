<?php

namespace Tests\Feature\Relationships;

use App\Models\Genre;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_booksリレーションが正しく定義されている(): void
    {
        $genre = Genre::with('books')->first();

        $this->assertNotEmpty($genre->books);
        $this->assertTrue($genre->books->contains($genre->books->first()));
    }
}
