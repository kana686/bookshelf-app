<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'user_id' => User::first(),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'published_date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=Book',
        ];
    }
}
