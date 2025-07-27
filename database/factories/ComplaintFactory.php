<?php

namespace Database\Factories;

use App\Models\ComplaintStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'category' => fake()->randomElement(['technical', 'billing', 'service', 'product', 'other']),
            'user_id' => User::factory(),
            'complaint_status_id' => function () {
                // Use existing status or create a default one
                return ComplaintStatus::firstOrCreate(
                    ['slug' => 'open'],
                    [
                        'name' => 'Open',
                        'color' => '#3B82F6',
                        'sort_order' => 1,
                        'is_active' => true
                    ]
                )->id;
            },
        ];
    }

    public function assigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_to' => User::factory(),
                'assigned_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function resolved(): static
    {
        return $this->state(function (array $attributes) {
            $resolvedStatus = ComplaintStatus::firstOrCreate(
                ['slug' => 'resolved'],
                [
                    'name' => 'Resolved',
                    'color' => '#10B981',
                    'sort_order' => 3,
                    'is_active' => true
                ]
            );

            return [
                'complaint_status_id' => $resolvedStatus->id,
                'assigned_to' => User::factory(),
                'assigned_at' => fake()->dateTimeBetween('-1 month', '-1 week'),
                'resolved_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }
}
