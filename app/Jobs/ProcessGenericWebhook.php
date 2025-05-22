<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Campaign;
use App\Models\DonationTransaction;
use App\Models\User;
use App\Notifications\DonationConfirmed;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessGenericWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $webhookPayload  incoming webhook payload from gateway
     */
    public function __construct(public readonly array $webhookPayload) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Started processing Generic webhook', ['payload' => $this->webhookPayload]);

        try {
            $eventType = $this->webhookPayload['event_type'] ?? null;
            $internalTransactionId = $this->webhookPayload['transaction_id'] ?? null;

            if ($internalTransactionId === null || $eventType === null) {
                Log::warning('Webhook payload missing essential data', ['payload' => $this->webhookPayload]);

                return;
            }

            /** @var ?DonationTransaction $transaction */
            $transaction = DonationTransaction::find($internalTransactionId);

            if ($transaction === null) {
                Log::warning('Webhook received for unknown internal transaction', ['payload' => $this->webhookPayload]);

                return;
            }

            match ($eventType) {
                'success' => $this->handleSuccessEvent($transaction),
                'failed' => $this->handleFailEvent(
                    $transaction,
                    PaymentStatus::FAILED,
                    $this->webhookPayload['failure_reason'] ?? 'Payment failed via gateway'
                ),
                'cancelled' => $this->handleFailEvent(
                    $transaction,
                    PaymentStatus::CANCELLED,
                    $this->webhookPayload['failure_reason'] ?? 'Payment cancelled via gateway'
                ),
                default => Log::info("Unhandled Generic webhook event type: {$eventType}", [
                    'transaction_id' => $transaction->id,
                    'donation_id' => $transaction->donation_id,
                ]),
            };

        } catch (Exception $e) {
            Log::error("Error processing Generic webhook: {$e->getMessage()}", ['payload' => $this->webhookPayload]);
            // retry
            throw $e;
        }
    }

    private function handleSuccessEvent(DonationTransaction $transaction): void
    {
        if (PaymentStatus::COMPLETED->value === $transaction->status) {
            Log::info("Transaction {$transaction->id} already marked as COMPLETED", [
                'donation_id' => $transaction->donation_id,
            ]);

            return;
        }

        $gatewayTransactionId = $this->webhookPayload['gateway_transaction_id'] ?? $transaction->gateway_transaction_id;

        DB::transaction(function () use ($transaction, $gatewayTransactionId) {
            $transaction->update([
                'status' => PaymentStatus::COMPLETED->value,
                'processed_at' => now(),
                'gateway_transaction_id' => $gatewayTransactionId,
                'failure_reason' => null,
            ]);

            $this->updateCampaignAmount($transaction);

            Log::info("Transaction {$transaction->id} and related campaign updated successfully", [
                'donation_id' => $transaction->donation_id,
                'gateway_transaction_id_used' => $transaction->gateway_transaction_id,
            ]);
        });

        $this->sendDonationConfirmation($transaction);
    }

    private function handleFailEvent(DonationTransaction $donationTransaction, PaymentStatus $targetStatus, string $failureReason): void
    {
        if ($donationTransaction->status !== PaymentStatus::PENDING->value) {
            Log::info("Transaction {$donationTransaction->id} is not in PENDING state. Skipping...", [
                'donation_id' => $donationTransaction->donation_id,
                'webhook_failure_reason' => $failureReason,
            ]);

            return;
        }

        DB::transaction(function () use ($donationTransaction, $targetStatus, $failureReason) {
            $donationTransaction->update([
                'status' => $targetStatus->value,
                'failure_reason' => $failureReason,
                'processed_at' => now(),
            ]);

            Log::info("Transaction {$donationTransaction->id} status updated to {$targetStatus->value}", [
                'donation_id' => $donationTransaction->donation_id,
                'failure_reason_logged' => $failureReason,
            ]);
        });
    }

    private function updateCampaignAmount(DonationTransaction $donationTransaction): void
    {
        if (! $donationTransaction->donation?->campaign) {
            Log::critical("Missing campaign information for transaction {$donationTransaction->id}!", [
                'donation_id' => $donationTransaction->donation_id,
            ]);

            throw new Exception('Missing campaign information for transaction');
        }

        /** @var Campaign $campaign */
        $campaign = $donationTransaction->donation->campaign;
        $campaign->increment('current_amount', $donationTransaction->amount);
    }

    private function sendDonationConfirmation(DonationTransaction $donationTransaction): void
    {
        if (! $donationTransaction->donation?->user) {
            Log::warning("Transaction {$donationTransaction->id} is missing user information for notification", [
                'donation_id' => $donationTransaction->donation_id,
            ]);

            return;
        }

        /** @var User $user */
        $user = $donationTransaction->donation->user;
        $user->notify(new DonationConfirmed($donationTransaction));
    }
}
