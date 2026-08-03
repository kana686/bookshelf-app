<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteService
{
    public function getPaginatedFavorites(Authenticatable $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->favoriteBooks()
            ->latest()
            ->paginate($perPage);
    }

    public function toggleFavorite(Book $book, Authenticatable $user): void
    {
        $book->usersWhoFavorited()->toggle($user->id);
    }
}
