<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Contracts\Auth\Authenticatable;

class LikeService
{
    public function toggleLike(Review $review, Authenticatable $user): void
    {
        if ($review->likedByUsers()->where('user_id', $user->id)->exists()) {
            $review->likedByUsers()->where('user_id', $user->id)->delete();
        } else {
            $review->likedByUsers()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
