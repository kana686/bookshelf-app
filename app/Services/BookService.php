<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookService
{
    public function getPaginatedBooks(int $perPage): LengthAwarePaginator
    {
        return Book::with('genres')->latest()->paginate($perPage);
    }

    public function getBookById(int $id): Book
    {
        return Book::with(['genres', 'reviews.user', 'usersWhoFavorited'])->findOrFail($id);
    }

    public function getAllGenres(): Collection
    {
        return Genre::all();
    }

    public function createBook(array $data): Book
    {
        $book = Book::create($data);

        if (isset($data['genres'])) {
            $book->genres()->sync($data['genres']);
        }

        return $book;
    }

    public function updateBook(Book $book, array $data): Book
    {
        $book->update($data);

        if (isset($data['genres'])) {
            $book->genres()->sync($data['genres']);
        }

        return $book;
    }

    public function deleteBook(Book $book): void
    {
        $book->delete();
    }

    public function searchBooks(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest();

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('author', 'like', '%'.$keyword.'%');
            });
        }

        $genreInput = $filters['genre_id'] ?? $filters['genre'] ?? null;

        if (! empty($genreInput)) {
            $genreIds = is_array($genreInput) ? $genreInput : [$genreInput];

            $query->whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            });
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        return $query->paginate($perPage);
    }
}
