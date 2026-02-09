<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\School;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 500, 10000),
            'method' => fake()->randomElement(['cash', 'gcash', 'bank_transfer', 'card']),
            'reference' => strtoupper(fake()->bothify('PAY-####-????')),
            'status' => 'completed',
            'paid_on' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
        ]);
    }

    public function gcash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'gcash',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
