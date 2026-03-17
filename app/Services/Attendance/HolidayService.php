<?php

namespace App\Services\Attendance;

use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HolidayService
{
    public function generateFromApi($year)
    {
        $response = Http::get(
            "https://libur.deno.dev/api?year=$year"
        );

        $holidays = $response->json();

        foreach ($holidays as $holiday) {

            Holiday::updateOrCreate(
                [
                    'holiday_date' => $holiday['date']
                ],
                [
                    'name' => $holiday['name'],
                    'type' => 'national',
                    'is_generated' => true
                ]
            );
        }
    }

    public function getAll()
    {
        return Holiday::orderBy('holiday_date')->get();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            return Holiday::create([
                'name' => $data['name'],
                'holiday_date' => $data['holiday_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null,
                'is_generated' => false
            ]);
        });
    }


    public function update(Holiday $holiday, array $data)
    {
        return DB::transaction(function () use ($holiday, $data) {

            $holiday->update([
                'name' => $data['name'],
                'holiday_date' => $data['holiday_date'],
                'type' => $data['type'],
                'description' => $data['description'] ?? null
            ]);

            return $holiday;
        });
    }


    public function delete(Holiday $holiday)
    {
        return DB::transaction(function () use ($holiday) {

            $holiday->delete();

            return true;
        });
    }
}
