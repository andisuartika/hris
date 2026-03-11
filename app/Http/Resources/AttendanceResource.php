<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'work_date' => $this->work_date,
            'clock_in_at' => $this->clock_in_at,
            'clock_out_at' => $this->clock_out_at,

            'clock_in_location' => [
                'lat' => $this->clock_in_lat,
                'lng' => $this->clock_in_lng
            ],

            'clock_out_location' => [
                'lat' => $this->clock_out_lat,
                'lng' => $this->clock_out_lng
            ],

            'status' => $this->status
        ];
    }
}
