<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'invoice_number' => fake()->unique()->bothify('INV-####-????'),
            'issue_date' => fake()->date(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement(\App\Enums\InvoiceStatus::class),
            'total_amount' => 0, // Will be calculated based on items
            'notes' => fake()->sentence(),
        ];
    }
}
