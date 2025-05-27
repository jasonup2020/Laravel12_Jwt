<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\DTOs\LoginData;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request)
    {
        $data = LoginData::fromRequest($request);
        $response = $this->authService->login($data);

        if (! $response['success']) {
            return $this->failedResponseJSON($response['message'], $response['status']);
        }

        return $this->successfulResponseJSON(null, $response['data'], $response['status']);
    }

    public function logout()
    {
        $this->authService->logout();
        return $this->successfulResponseJSON('The access token has been successfully invalidated');
    }

    public function check()
    {
        $data = $this->authService->check();
        return $this->successfulResponseJSON(null, $data);
    }
}
