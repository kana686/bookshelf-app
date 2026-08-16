<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getReportData(User $user): array
    {
        $reviewsQuery = $user->reviews();
        $totalReviews = (clone $reviewsQuery)->count();
        $booksRead = (clone $reviewsQuery)->distinct('book_id')->count('book_id');
        $averageRating = (clone $reviewsQuery)->avg('rating') ?? 0;

        $summary = [
            'total_reviews' => $totalReviews,
            'books_read' => $booksRead,
            'average_rating' => $averageRating,
        ];

        $distributionData = (clone $reviewsQuery)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = collect(range(1, 5))->map(function ($star) use ($distributionData) {
            return $distributionData->get($star, 0);
        });

        $topRatedBooks = $user->reviews()
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($review, $index) {
                return [
                    'id' => $review->book->id ?? null,
                    'title' => $review->book->title ?? '不明な書籍',
                    'author' => $review->book->author ?? '',
                    'rating' => $review->rating,
                    'rank' => $index + 1,
                ];
            })
            ->toArray();

        $genreRatings = DB::table('reviews')
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $user->id)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('count(reviews.id) as count'),
                DB::raw('avg(reviews.rating) as average_rating')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('average_rating', 'desc')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'count' => $item->count,
                    'average_rating' => $item->average_rating,
                    'rank' => $index + 1,
                ];
            })
            ->toArray();

        return [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];
    }
}
