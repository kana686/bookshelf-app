<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        if ($users->isEmpty() || $reviews->isEmpty()) {
            return;
        }

        foreach ($reviews as $review) {
            $targetUsers = $users->reject(function ($user) use ($review) {
                return $user->id === $review->user_id;
            });

            $count = min(rand(0, 3), $targetUsers->count());

            if ($count > 0) {
                $likedUsers = $targetUsers->random($count);

                $review->likedByUsers()->syncWithoutDetaching($likedUsers);
            }
        }
    }
}
