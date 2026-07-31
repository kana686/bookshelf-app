<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Genre;

class BookService
{
    public function getPaginatedBooks(int $perPage = 10)
    {
        return Book::with('genres')->latest()->paginate($perPage);
    }

    public function getBookById(int $id)
    {
        return Book::with(['genres', 'reviews.user', 'usersWhoFavorited'])->findOrFail($id);
    }

    public function getAllGenres()
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
}
