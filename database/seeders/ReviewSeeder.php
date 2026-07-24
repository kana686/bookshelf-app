<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        foreach ($books as $book) {
            $count = rand(2, 4);

            Review::factory()->count($count)->create([
                'book_id' => $book->id,
                'user_id' => fn () => $users->random()->id,
            ]);
        }
    }
}
