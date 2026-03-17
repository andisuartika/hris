<?php

namespace App\Http\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\Attendance\HolidayService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    // service holiday
    public function __construct(private HolidayService $service) {}

    public function index()
    {
        $holidays = $this->service->getAll();

        return view('pages.holidays.index', compact('holidays'));
    }

    public function generate($year)
    {
        $this->service->generateFromApi($year);

        return back()->with('success', 'Hari libur berhasil digenerate');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'holiday_date' => 'required|date',
            'type' => 'required'
        ]);

        $this->service->create($request->all());

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan');
    }


    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required',
            'holiday_date' => 'required|date',
            'type' => 'required'
        ]);

        $this->service->update($holiday, $request->all());

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Hari libur berhasil diupdate');
    }

    public function destroy(Holiday $holiday)
    {
        $this->service->delete($holiday);

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Hari libur berhasil dihapus');
    }
}
