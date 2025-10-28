<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        return [
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(12),
            'content' => fake()->paragraphs(3, true),
        ];
    }
}
