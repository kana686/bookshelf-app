<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApiBookRequest;
use App\Http\Requests\Api\V1\BookSearchRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
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

        $perPage = (int) $request->input('per_page', 20);

        $books = $this->bookService->searchBooks($filters, $perPage);

        return BookResource::collection($books)->response();
    }

    public function show(int $id): JsonResponse
    {
        $book = $this->bookService->getBookById($id);

        return (new BookResource($book))->response();
    }

    public function store(ApiBookRequest $request): JsonResponse
    {
        $data = $request->validated();

        $book = $this->bookService->createBook($data);

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    public function update(ApiBookRequest $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $updatedBook = $this->bookService->updateBook($book, $request->validated());

        return (new BookResource($updatedBook))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);

        $this->bookService->deleteBook($book);

        return response()->json(null, 204);
    }
}
