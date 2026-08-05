<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Services\GenreService;
use Illuminate\View\View;

class GenreController extends Controller
{
    protected GenreService $genreService;

    public function __construct(GenreService $genreService)
    {
        $this->genreService = $genreService;
    }

    public function index(): View
    {
        $genres = $this->genreService->getGenresWithBookCount();

        return view('genres.index', compact('genres'));
    }

    public function show(Genre $genre): View
    {
        $books = $this->genreService->getGenreWithBooks($genre);

        return view('genres.show', compact('genre', 'books'));
    }
}
