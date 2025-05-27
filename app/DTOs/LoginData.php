<?php

namespace App\DTOs;

class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public string $ip
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->input('email'),
            password: $request->input('password'),
            ip: $request->ip()
        );
    }
}
