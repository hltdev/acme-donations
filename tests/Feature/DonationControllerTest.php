<?php

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->create();
});

it('returns a list of donations', function () {
    actingAs($this->user);

    Donation::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = getJson('/api/donations');

    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'amount', 'comment', 'campaign'],
        ],
        'links',
        'meta',
    ]);
});

it('returns specific donation details', function () {
    actingAs($this->user);

    $donation = Donation::factory()->create([
        'user_id' => $this->user->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = getJson("/api/donations/{$donation->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Donation details retrieved successfully.',
        'donation' => [
            'id' => $donation->id,
            'amount' => (string) $donation->amount,
        ],
    ]);
});

it('returns 404 when donation not found', function () {
    actingAs($this->user);

    $response = getJson('/api/donations/999');

    $response->assertStatus(404);
});

it('validates required fields', function () {
    actingAs($this->user);

    $response = postJson('/api/donations', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['campaign_id', 'amount', 'payment_gateway']);
});
