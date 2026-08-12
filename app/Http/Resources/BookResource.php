<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'genre' => $this->whenLoaded('genres', function () {
                $genre = $this->genres->first();

                return $genre ? [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ] : null;
            }),
            'reviews_avg_rating' => $this->reviews_avg_rating ? round((float) $this->reviews_avg_rating, 1) : null,
            'reviews_count' => $this->reviews_count ?? 0,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
