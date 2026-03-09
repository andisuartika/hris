<?php

namespace App\DTO\Master;

class PositionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $company_id,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated('name'),
            company_id: $request->validated('company_id'),
        );
    }
}
