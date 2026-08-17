<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    public function searchByIsbn(string $isbn)
    {
        $apiKey = config('services.google.books.api_key');
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => "isbn:{$isbn}",
            'key' => $apiKey,
        ]);

        if ($response->failed() || empty($response->json('items'))) {
            return null;
        }

        $bookData = $response->json('items')[0]['volumeInfo'] ?? [];

        return [
            'title' => $bookData['title'] ?? null,
            'author' => isset($bookData['authors']) ? implode(', ', $bookData['authors']) : null,
            'description' => $bookData['description'] ?? null,
            'image_url' => $bookData['imageLinks']['thumbnail'] ?? null,
            'published_date' => $bookData['publishedDate'] ?? null,
        ];
    }
}
