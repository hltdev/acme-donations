<?php

namespace App\Contracts;

use App\Exceptions\PaymentFailedException;
use App\Models\DonationTransaction;

interface PaymentGatewayInterface
{
    /**
     * Initiates a payment process with a specific gateway
     *
     * @param  DonationTransaction  $transaction  The associated donation transaction
     * @param  array<string, mixed>  $paymentDetails  contains details required for the payment
     * @return array<string, mixed> returns array with gateway redirect URL, transaction ID...
     *
     * @throws PaymentFailedException
     */
    public function initiatePayment(DonationTransaction $transaction, array $paymentDetails): array;

    /**
     * Handles incoming webhook from payment gateway
     *
     * @param  array<string, mixed>  $payload  The incoming webhook payload
     */
    public function handleWebhook(array $payload): void;
}
