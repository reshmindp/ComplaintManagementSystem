<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ComplaintStatusFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Open', 'In Progress', 'Pending Review', 'Under Investigation',
            'Waiting Customer', 'Resolved', 'Closed', 'Cancelled'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
