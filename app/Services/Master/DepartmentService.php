<?php

namespace App\Services\Master;

use App\Models\Department;
use App\DTO\Master\DepartmentDTO;

class DepartmentService
{

    public function getAll()
    {
        return Department::all();
    }

    public function create(DepartmentDTO $dto)
    {
        return Department::create([
            'name' => $dto->name,
            'company_id' => $dto->company_id,
        ]);
    }

    public function getById($id)
    {
        return Department::findOrFail($id);
    }

    public function update($id, DepartmentDTO $dto)
    {
        $department = $this->getById($id);
        return $department->update([
            'name' => $dto->name,
            'company_id' => $dto->company_id,
        ]);
    }

    public function delete($id)
    {
        $department = $this->getById($id);
        return $department->delete();
    }
}
