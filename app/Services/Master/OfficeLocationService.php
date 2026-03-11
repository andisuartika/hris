<?php

namespace App\Services\Master;

use App\Models\OfficeLocation;
use App\DTO\Master\OfficeLocationDTO;

class OfficeLocationService
{
    public function getAll()
    {
        return OfficeLocation::with('company')->latest()->get();
    }

    public function getById(int $id)
    {
        return OfficeLocation::with('company')->findOrFail($id);
    }

    public function create(OfficeLocationDTO $dto)
    {
        return OfficeLocation::create($dto->toArray());
    }

    public function update(int $id, OfficeLocationDTO $dto)
    {
        $location = OfficeLocation::findOrFail($id);
        return $location->update($dto->toArray());
    }

    public function delete(int $id)
    {
        return OfficeLocation::destroy($id);
    }
}
