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
            $targetUsers = $users->random(min(2, $users->count()));
            foreach ($targetUsers as $user) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        $remainingReviews = 32 - ($books->count() * 2);
        $createdCount = 0;

        while ($createdCount < $remainingReviews) {
            $availableBooks = $books->filter(function ($book) {
                return $book->reviews()->count() < 4;
            });

            if ($availableBooks->isEmpty()) {
                break;
            }

            $book = $availableBooks->random();
            $user = $users->random();

            $exists = Review::where('book_id', $book->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                ]);
                $createdCount++;
            }
        }
    }
}
