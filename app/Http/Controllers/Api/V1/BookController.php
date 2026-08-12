<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookSearchRequest;
use App\Services\BookService;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    protected BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    public function index(BookSearchRequest $request): JsonResponse
    {
        $filters = $request->only(['keyword', 'genre_id']);

        $perPage = (int) $request->input('per_page', 10);

        $books = $this->bookService->searchBooks($filters, $perPage);

        return response()->json($books);
    }

    public function show(int $id): JsonResponse
    {
        $book = $this->bookService->getBookById($id);

        return response()->json($book);
    }
}
