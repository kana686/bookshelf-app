<?php

namespace App\Services;

use App\Models\Book;

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
}
