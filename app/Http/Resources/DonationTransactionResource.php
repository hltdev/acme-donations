<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\DonationTransaction
 */
class DonationTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'donation_id' => $this->donation_id,
            'gateway_name' => $this->gateway_name,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'failure_reason' => $this->failure_reason,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
