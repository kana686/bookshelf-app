<?php

namespace App\Services;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GenreService
{
    public function getGenresWithBookCount(): Collection
    {
        return Genre::withCount('books')->get();
    }

    public function getGenreWithBooks(Genre $genre): LengthAwarePaginator
    {
        return $genre->books()->paginate(10);
    }
}
