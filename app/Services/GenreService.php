<?php

namespace App\Services;

use App\Models\Genre;
use Illuminate\Pagination\LengthAwarePaginator;

class GenreService
{
    public function getPaginatedGenresWithBookCount(int $perPage = 10): LengthAwarePaginator
    {
        return Genre::withCount('books')->paginate($perPage);
    }

    public function getGenreWithBooks(Genre $genre): LengthAwarePaginator
    {
        return $genre->books()->paginate(10);
    }

    public function createGenre(array $data): Genre
    {
        return Genre::create($data);
    }

    public function updateGenre(Genre $genre, array $data): bool
    {
        return $genre->update($data);
    }

    public function deleteGenre(Genre $genre): bool
    {
        if ($genre->books()->count() > 0) {
            return false;
        }

        return $genre->delete();
    }
}
