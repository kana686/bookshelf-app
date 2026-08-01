<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        $this->reviewService->storeReview($book, $request->validated());

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました');
    }

    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $this->reviewService->updateReview($review, $request->validated());

        return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $bookId = $review->book_id;
        $this->reviewService->deleteReview($review);

        return redirect()->route('books.show', $bookId)->with('success', 'レビューを削除しました');
    }
}
