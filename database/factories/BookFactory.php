<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('ja_JP');

        return [
            'title' => $faker->sentence(3),
            'author' => $faker->name(),
            'isbn' => $faker->unique()->numerify('#############'),
            'published_date' => $faker->date(),
            'description' => $faker->realText(100),
            'image_url' => $faker->imageUrl(200, 300),
            'user_id' => User::factory(),
        ];
    }
}
