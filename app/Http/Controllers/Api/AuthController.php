<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TurnstileVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(
        RegisterRequest $request,
        TurnstileVerifier $turnstile,
        ActivityLogger $activityLogger,
    ): JsonResponse {
        // $secretKey = config('services.turnstile.secret_key');
        //  dd($secretKey);
        $this->ensureTurnstileIsValid($request, $turnstile, 'register');

        $user = User::create($request->safe()->only(['name', 'email', 'password']));
        $user->refresh();
        $activityLogger->record($user, 'user.registered', 'user', $user->id, $user->name);

        return $this->tokenResponse($user, 201);
    }

    public function login(LoginRequest $request, TurnstileVerifier $turnstile): JsonResponse
    {
        $this->ensureTurnstileIsValid($request, $turnstile, 'login');

        $credentials = $request->validated();

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return $this->tokenResponse($user);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'You have been signed out.']);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken('marketplace-web')->plainTextToken,
            'user' => $user,
        ], $status);
    }

    private function ensureTurnstileIsValid(Request $request, TurnstileVerifier $turnstile, string $action): void
    {
        if ($turnstile->verify(
            (string) $request->input('cf-turnstile-response'),
            $request->ip(),
            $action,
        )) {
            return;
        }

        throw ValidationException::withMessages([
            'cf-turnstile-response' => ['Please complete the security check and try again.'],
        ]);
    }
}
