<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookService;

class BookController extends Controller
{
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
}
