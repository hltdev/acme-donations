<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Attempt to login and issue an API access token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'token' => $token,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    /**
     * Store a newly created user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
        ]);

        return response()->json([
            'message' => 'Registeration successful',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Display logged-in user information
     */
    public function profile(): JsonResponse
    {
        return response()->json(new UserResource(Auth::user()));
    }

    /**
     * Log out and revoke API access token
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
