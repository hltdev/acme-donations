<?php

namespace App\Services\Payment\Strategies;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentFailedException;
use App\Jobs\ProcessGenericWebhook;
use App\Models\DonationTransaction;
use Illuminate\Support\Facades\Log;

class GenericPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Initiates a payment process for a generic gateway
     *
     * @param  array<string, mixed>  $paymentDetails
     * @return array<string, mixed>
     *
     * @throws PaymentFailedException
     */
    public function initiatePayment(DonationTransaction $transaction, array $paymentDetails): array
    {
        Log::info("Initiating payment for generic gateway for transaction: {$transaction->id}");

        $transaction->update([
            'gateway_transaction_id' => 'generic_txid_'.uniqid(),
            'status' => PaymentStatus::PENDING,
            'payment_method' => $paymentDetails['method'] ?? 'unknown',
        ]);

        return [
            'data' => [
                'gateway_tx_id' => $transaction->gateway_transaction_id,
                'redirect_url' => 'https://generic-gateway.com/checkout/'.$transaction->gateway_transaction_id,
                'message' => 'Please complete payment on the external gateway.',
            ],
            'status' => PaymentStatus::PENDING,
        ];
    }

    /**
     * Handles webhooks from a generic payment gateway
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleWebhook(array $payload): void
    {
        Log::info('Dispatching Generic Webhook Payload: ', $payload);

        ProcessGenericWebhook::dispatch($payload);
    }
}
