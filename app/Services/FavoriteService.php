<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Contracts\Auth\Authenticatable;

class FavoriteService
{
    public function toggleFavorite(Book $book, Authenticatable $user): void
    {
        $book->usersWhoFavorited()->toggle($user->id);
    }
}
