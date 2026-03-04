<?php

namespace App\DTO\Auth;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            email: $request->email,
            password: $request->password,
            deviceName: $request->device_name,
        );
    }
}
