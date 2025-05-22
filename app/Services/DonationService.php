<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentFailedException;
use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\User;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;

class DonationService
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Process a new donation
     *
     * @param  array<string, mixed>  $paymentDetails
     * @return array<string, mixed>
     *
     * @throws PaymentFailedException If payment initiation fails
     */
    public function processNewDonation(
        User $user,
        int $campaignId,
        float $amount,
        string $currency,
        ?string $comment,
        string $paymentGateway,
        array $paymentDetails
    ): array {
        /** @var mixed $result */
        $result = DB::transaction(function () use ($user, $campaignId, $amount, $currency, $comment, $paymentGateway, $paymentDetails) {

            $donation = $this->createDonation($user, $campaignId, $amount, $comment);
            $transaction = $this->createDonationTransaction($donation, $paymentGateway, $amount, $currency);

            try {
                $paymentInitiationData = $this->paymentService->initiatePayment(
                    $paymentGateway,
                    $transaction,
                    $paymentDetails
                );

                return [
                    'donation' => $donation,
                    'payment_data' => $paymentInitiationData['data'],
                    'transaction_status' => $paymentInitiationData['status']->value,
                ];

            } catch (PaymentFailedException $e) {
                $transaction->update([
                    'status' => PaymentStatus::FAILED,
                    'failure_reason' => $e->getMessage(),
                    'processed_at' => now(),
                ]);
                // roll back
                throw $e;
            }
        });

        if ($result === null || is_array($result) === false) {
            throw new PaymentFailedException;
        }

        return $result;
    }

    protected function createDonation(User $user, int $campaignId, float $amount, ?string $comment): Donation
    {
        return $user->donations()->create([
            'campaign_id' => $campaignId,
            'amount' => $amount,
            'comment' => $comment,
        ]);
    }

    protected function createDonationTransaction(Donation $donation, string $gatewayName, float $amount, string $currency): DonationTransaction
    {
        return $donation->transactions()->create([
            'gateway_name' => $gatewayName,
            'amount' => $amount,
            'currency' => $currency,
            'status' => PaymentStatus::INITIATED, // initial status
        ]);
    }
}
