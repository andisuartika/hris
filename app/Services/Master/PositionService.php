<?php

namespace App\Services\Master;

use App\Models\Position;
use App\DTO\Master\PositionDTO;

class PositionService
{

    public function getAll()
    {
        return Position::all();
    }

    public function create(PositionDTO $dto)
    {
        return Position::create([
            'name' => $dto->name,
            'company_id' => $dto->company_id,
        ]);
    }

    public function getById($id)
    {
        return Position::findOrFail($id);
    }

    public function update($id, PositionDTO $dto)
    {
        $position = $this->getById($id);
        return $position->update([
            'name' => $dto->name,
            'company_id' => $dto->company_id,
        ]);
    }

    public function delete($id)
    {
        $position = $this->getById($id);
        return $position->delete();
    }
}
