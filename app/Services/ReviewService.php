<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    public function storeReview(Book $book, array $data): Review
    {
        return $book->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    public function updateReview(Review $review, array $data): bool
    {
        return $review->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    public function deleteReview(Review $review): ?bool
    {
        return $review->delete();
    }
}
