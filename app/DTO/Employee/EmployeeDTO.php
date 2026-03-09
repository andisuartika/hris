<?php

namespace App\DTO\Employee;

class EmployeeDTO
{
    public function __construct(
        public string $employeeCode,
        public string $fullName,
        public string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $password,
        public ?int $officeLocationId,
        public int $companyId,
        public string $status,
        public ?int $departmentId,
        public ?int $positionId,
        public ?string $joinDate,
        public ?string $contractStart,
        public ?string $contractEnd,
        public ?array $officeLocations
    ) {}
}
