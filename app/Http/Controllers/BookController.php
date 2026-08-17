<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    protected BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['keyword', 'genre', 'sort']);

        $books = $this->bookService->searchBooks($filters, 10);

        $genres = $this->bookService->getAllGenres();

        return view('books.index', compact('books', 'genres'));
    }

    public function show(Book $book): View
    {
        $book = $this->bookService->getBookById($book->id);

        return view('books.show', compact('book'));
    }

    public function create(): View
    {
        $genres = $this->bookService->getAllGenres();

        return view('books.create', compact('genres'));
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $book = $this->bookService->createBook($data);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました');
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $book = $this->bookService->getBookById($book->id);
        $genres = $this->bookService->getAllGenres();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $this->bookService->updateBook($book, $request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $this->bookService->deleteBook($book);

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}
