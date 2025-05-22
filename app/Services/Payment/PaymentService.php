<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentFailedException;
use App\Models\DonationTransaction;
use App\Services\Payment\Strategies\GenericPaymentGateway;
use InvalidArgumentException;

class PaymentService
{
    /**
     * @var array<string, string>
     */
    protected array $gateways = [
        'generic' => GenericPaymentGateway::class,
    ];

    /**
     * Initiates a payment using a given gateway strategy
     *
     * @param  array<string, mixed>  $paymentDetails
     * @return array<string, mixed>
     *
     * @throws PaymentFailedException
     * @throws InvalidArgumentException
     */
    public function initiatePayment(string $gatewayName, DonationTransaction $transaction, array $paymentDetails): array
    {
        if (! isset($this->gateways[$gatewayName])) {
            throw new InvalidArgumentException("Unsupported payment gateway: {$gatewayName}");
        }

        $gateway = app($this->gateways[$gatewayName]);

        if (! $gateway instanceof PaymentGatewayInterface) {
            throw new InvalidArgumentException("Gateway class {$this->gateways[$gatewayName]} does not implement PaymentGateway contract.");
        }

        return $gateway->initiatePayment($transaction, $paymentDetails);
    }
}
