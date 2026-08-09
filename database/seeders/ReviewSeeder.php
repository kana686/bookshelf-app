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

        $totalCreated = 0;
        $targetTotal = 32;

        foreach ($books as $index => $book) {
            $remainingBooks = $books->count() - $index;
            $remainingReviewsNeeded = $targetTotal - $totalCreated;

            $minCount = max(2, $remainingReviewsNeeded - ($remainingBooks - 1) * 4);
            $maxCount = min(4, $remainingReviewsNeeded - ($remainingBooks - 1) * 2);
            $count = rand(max(2, $minCount), min(4, $maxCount));

            $targetUsers = $users->random(min($count, $users->count()));

            foreach ($targetUsers as $user) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                ]);
                $totalCreated++;
            }
        }
    }
}
