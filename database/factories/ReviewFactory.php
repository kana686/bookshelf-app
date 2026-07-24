<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'book_id' => Book::inRandomOrder()->first()?->id ?? Book::factory(),
            'rating' => $this->faker->numberBetween(3, 5),
            'comment' => $this->faker->realText(50),
        ];
    }
}
