<?php

use App\Exceptions\PaymentFailedException;
use App\Models\DonationTransaction;
use App\Services\Payment\PaymentService;
use App\Services\Payment\Strategies\GenericPaymentGateway;

it('initiates a payment successfully with correct gateway', function () {
    $gatewayName = 'generic';
    $transaction = mock(DonationTransaction::class);
    $paymentDetails = ['token' => 'test_token', 'amount' => 1000];
    $expectedResponse = ['status' => 'success', 'transaction_id' => 'txn_123'];

    $mockGateway = mock(GenericPaymentGateway::class);
    $mockGateway->shouldReceive('initiatePayment')
        ->once()
        ->withArgs(function ($argTransaction, $argPaymentDetails) use ($transaction, $paymentDetails) {
            return $argTransaction === $transaction && $argPaymentDetails === $paymentDetails;
        })
        ->andReturn($expectedResponse);

    // bind mock to container
    app()->instance(GenericPaymentGateway::class, $mockGateway);

    $paymentService = new PaymentService;

    $response = $paymentService->initiatePayment($gatewayName, $transaction, $paymentDetails);

    expect($response)->toBe($expectedResponse);
});

it('throws InvalidArgumentException for unsupported gateway', function () {
    $gatewayName = 'unsupported_gateway';
    $transaction = mock(DonationTransaction::class);
    $paymentDetails = ['token' => 'test_token'];

    $paymentService = new PaymentService;

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("Unsupported payment gateway: {$gatewayName}");

    $paymentService->initiatePayment($gatewayName, $transaction, $paymentDetails);
});

it('throws PaymentFailedException if the gateway fails', function () {
    $gatewayName = 'generic';
    $transaction = mock(DonationTransaction::class);
    $paymentDetails = ['token' => 'test_token'];

    $mockGateway = mock(GenericPaymentGateway::class);
    $mockGateway->shouldReceive('initiatePayment')
        ->once()
        ->andThrow(new PaymentFailedException);

    // bind mock to container
    app()->instance(GenericPaymentGateway::class, $mockGateway);

    $paymentService = new PaymentService;

    $this->expectException(PaymentFailedException::class);

    $paymentService->initiatePayment($gatewayName, $transaction, $paymentDetails);
});
