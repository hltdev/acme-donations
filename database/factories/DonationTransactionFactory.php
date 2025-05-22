<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationTransaction>
 */
class DonationTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(PaymentStatus::cases());

        return [
            'donation_id' => Donation::factory(),
            'gateway_name' => 'generic',
            'gateway_transaction_id' => 'txid_'.Str::random(20),
            'payment_method' => fake()->randomElement(['card', 'bank_transfer']),
            'status' => $status,
            'amount' => fake()->randomFloat(2, 5, 10000),
            'currency' => 'EUR',
            'failure_reason' => $status === PaymentStatus::FAILED ? fake()->sentence() : null,
            'processed_at' => $status === PaymentStatus::COMPLETED ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
