<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentFailedException;
use App\Http\Requests\Donation\StoreRequest;
use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonationService;
use App\Services\Payment\Strategies\GenericPaymentGateway;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DonationController extends Controller
{
    public function __construct(protected DonationService $donationService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPageInput = $request->input('per_page');
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 5;
        $donations = Donation::latest()->with(['campaign'])->paginate($perPage);

        return DonationResource::collection($donations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $campaignId = $request->validated('campaign_id');
        $amount = $request->validated('amount');
        $comment = $request->validated('comment');
        $paymentGateway = $request->validated('payment_gateway');

        try {
            $result = $this->donationService->processNewDonation(
                $user,
                $campaignId,
                $amount,
                'EUR', // by default
                $comment,
                $paymentGateway,
                $request->all()
            );

            // data for the frontend to handle the payment flow
            return response()->json([
                'message' => 'Donation initiated successfully. Awaiting payment.',
                'donation' => new DonationResource($result['donation']),
                'payment_data' => $result['payment_data'],
            ], 200);

        } catch (PaymentFailedException $e) {
            return response()->json(['message' => 'Payment initiation failed'], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Donation $donation): JsonResponse
    {
        $donation->load(['campaign', 'transactions']);

        return response()->json([
            'message' => 'Donation details retrieved successfully.',
            'donation' => new DonationResource($donation),
        ]);
    }

    public function handleWebhook(string $gateway): JsonResponse
    {
        $payload = request()->all(); // Get the raw payload
        Log::info("Webhook received for gateway: {$gateway}", ['payload' => $payload]);

        try {
            $gatewayClass = match ($gateway) {
                'generic' => GenericPaymentGateway::class,
                default => throw new InvalidArgumentException("Unsupported webhook gateway: {$gateway}"),
            };

            $specificGateway = app($gatewayClass);
            $specificGateway->handleWebhook($payload);

            return response()->json(['message' => 'Webhook received'], 200);
        } catch (Exception $e) {
            Log::error("Webhook processing error for {$gateway}: ".$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['message' => 'Error processing webhook.'], 500);
        }
    }
}
