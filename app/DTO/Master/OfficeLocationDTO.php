<?php

namespace App\DTO\Master;

class OfficeLocationDTO
{
    public string $name;
    public int $company_id;
    public float $latitude;
    public float $longitude;
    public int $radius;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->company_id = $data['company_id'];
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->radius = $data['radius_meters']; // Mendukung kedua nama field
    }

    public static function from(array $data): self
    {
        return new self($data);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'company_id' => $this->company_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radius,
        ];
    }
}
