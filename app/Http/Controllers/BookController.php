<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookController extends Controller
{
    use AuthorizesRequests;

    protected BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function index()
    {
        $books = $this->bookService->getPaginatedBooks();

        return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        $book = $this->bookService->getBookById($book->id);

        return view('books.show', compact('book'));
    }

    public function create()
    {
        return view('books.create');
    }

    public function store(BookRequest $request)
    {
        $book = $this->bookService->createBook($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました');
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        return view('books.edit', compact('book'));
    }

    public function update(BookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $this->bookService->updateBook($book, $request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $this->bookService->deleteBook($book);

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}
