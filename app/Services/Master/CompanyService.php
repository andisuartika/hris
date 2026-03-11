<?php

namespace App\Services\Master;

use App\Models\Company;
use App\DTO\Company\CompanyDTO;

class CompanyService
{
    public function getAll()
    {
        return Company::latest()->get();
    }

    public function createCompany(CompanyDTO $dto): Company
    {
        return Company::create([
            'name'     => $dto->name,
            'timezone' => $dto->timezone
        ]);
    }

    public function updateCompany(Company $company, CompanyDTO $dto): bool
    {
        return $company->update([
            'name'     => $dto->name,
            'timezone' => $dto->timezone
        ]);
    }
}
