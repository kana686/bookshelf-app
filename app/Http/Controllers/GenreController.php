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

    public function create(): View
    {
        return view('genres.create');
    }

    public function store(GenreRequest $request): RedirectResponse
    {
        $this->genreService->createGenre($request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを追加しました');
    }

    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $this->genreService->updateGenre($genre, $request->validated());

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        $result = $this->genreService->deleteGenre($genre);

        if (! $result) {
            return redirect()->route('genres.index')->with('error', '書籍が紐づいているため削除できません');
        }

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
    }
}
