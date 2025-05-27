<?php

namespace App\Services;

use App\DTOs\LoginData;

use Illuminate\Support\Facades\RateLimiter;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;

class AuthService
{
    public function login(LoginData $data): array
    {
        $throttleKey = 'auth:attempts:' . $data->ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return [
                'success' => false,
                'message' => 'Too many login attempts. Please try again later in ' . $seconds . ' seconds',
                'status' => 429,
            ];
        }

        $credentials = [
            'email' => $data->email,
            'password' => $data->password
        ];

        $token = auth()->attempt($credentials);

        if (! $token) {
            RateLimiter::hit($throttleKey);
            return [
                'success' => false,
                'message' => 'Oops, your credentials does not match our records',
                'status' => 404,
            ];
        }

        RateLimiter::clear($throttleKey);

        return [
            'success' => true,
            'status' => 201,
            'data' => [
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => auth()->factory()->getTTL() . ' minutes'
                ]
            ]
        ];
    }

    public function logout(): void
    {
        $token = JWTAuth::getToken()->get();

        if ($token) {
            JWTAuth::invalidate(true);
        }

        auth()->logout();
    }

    public function check(): array
    {
        $token = JWTAuth::getToken()->get();
        $payload = JWTAuth::getPayload($token);

        return [
            'token' => $token,
            'issued_at' => Carbon::createFromTimestamp($payload->get('iat'))->toDateTimeString(),
            'expires_at' => Carbon::createFromTimestamp($payload->get('exp'))->toDateTimeString(),
            'ttl_minutes' => ($payload->get('exp') - $payload->get('iat')) / 60,
        ];
    }
}
