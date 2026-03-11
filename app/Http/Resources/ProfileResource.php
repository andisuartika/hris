<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $employee = $this->employee;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->getRoleNames()->first(),

            'employee' => [
                'employee_code' => $employee?->employee_code,
                'full_name' => $employee?->full_name,
                'email' => $employee?->email,
                'phone' => $employee?->phone,
                'address' => $employee?->address,
                'status' => $employee?->status,
                'join_date' => $employee?->join_date,

                'department' => [
                    'id' => $employee?->department?->id,
                    'name' => $employee?->department?->name,
                ],

                'position' => [
                    'id' => $employee?->position?->id,
                    'name' => $employee?->position?->name,
                ],

                'office_location' => [
                    'id' => $employee?->officeLocation?->id,
                    'name' => $employee?->officeLocation?->name,
                ],
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
