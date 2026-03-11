<?php

namespace App\DTO\Company;

class CompanyDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $timezone,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            timezone: $request->validated('timezone')
        );
    }
}
