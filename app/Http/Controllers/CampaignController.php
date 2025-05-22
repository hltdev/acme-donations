<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campaign\StoreRequest;
use App\Http\Requests\Campaign\UpdateRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns with pagination
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPageInput = $request->input('per_page');
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 5;

        return CampaignResource::collection(Campaign::latest()->paginate($perPage));
    }

    /**
     * Store a newly created campagin
     */
    public function store(StoreRequest $request): JsonResource
    {
        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        return new CampaignResource($campaign->load('user'));
    }

    /**
     * Display the specified campagin
     */
    public function show(Campaign $campaign): JsonResource
    {
        return new CampaignResource($campaign->load(['user', 'donations.user']));
    }

    /**
     * Update the specified campaign in storage
     */
    public function update(UpdateRequest $request, Campaign $campaign): JsonResource|JsonResponse
    {
        if (Auth::user()?->cannot('update', $campaign)) {
            return response()->json(['message' => 'You can only update your campaigns'], 403);
        }

        $campaign->update($request->validated());

        return new CampaignResource($campaign->load('user'));
    }

    /**
     * Remove the specified campaign from storage
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        if (Auth::user()?->cannot('delete', $campaign)) {
            return response()->json(['message' => 'You can only delete your campaigns'], 403);
        }

        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted successfully'], 200);
    }
}
