<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BookService
{
    public function getPaginatedBooks(int $perPage = 10): LengthAwarePaginator
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
        $data['user_id'] = Auth::id();

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

    public function searchBooks(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest();

        if (! empty($filters['keyword'])) {
            $query->where('title', 'like', '%'.$filters['keyword'].'%');
        }

        if (! empty($filters['genre_id'])) {
            $query->whereHas('genres', function ($q) use ($filters) {
                $q->whereIn('genres.id', $filters['genre_id']);
            });
        }

        return $query->paginate($perPage);
    }
}
