<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Contracts\Auth\Authenticatable;

class LikeService
{
    public function toggleLike(Review $review, Authenticatable $user): void
    {
        $review->likedByUsers()->toggle($user->id);
    }
}
