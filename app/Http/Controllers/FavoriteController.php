<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\FavoriteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    protected FavoriteService $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function index(Request $request): View
    {
        $books = $this->favoriteService->getPaginatedFavorites($request->user());

        return view('favorites.index', compact('books'));
    }

    public function store(Request $request, Book $book): RedirectResponse
    {
        $this->favoriteService->toggleFavorite($book, $request->user());

        return back();
    }
}
