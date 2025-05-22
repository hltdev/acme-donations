<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('registers a new user successfully', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = postJson('/api/register', $userData);

    $response->assertStatus(201);
    $response->assertJsonFragment(['message' => 'Registeration successful']);
    $response->assertJsonStructure([
        'message',
        'user' => ['id', 'name', 'email'],
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

it('logs in a user successfully', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = [
        'email' => 'login@example.com',
        'password' => 'password123',
    ];

    $response = postJson('/api/login', $credentials);
    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Login successful']);
    $response->assertJsonStructure([
        'message',
        'user' => ['id', 'name', 'email'],
        'token',
    ]);
    $response->assertJsonPath('user.id', $user->id);
});

it('displays user information if authenticated', function () {
    $user = User::factory()->create();

    actingAs($user, 'sanctum');

    $response = getJson('/api/profile');

    $response->assertOk();
    $response->assertJsonStructure(['id', 'name', 'email']);
    $response->assertJsonPath('id', $user->id);
    $response->assertJsonPath('email', $user->email);
});
