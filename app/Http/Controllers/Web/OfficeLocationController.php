<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Master\OfficeLocationService;
use App\Services\Master\CompanyService;
use App\DTO\Master\OfficeLocationDTO;
use App\Http\Requests\Master\OfficeLocationRequest;

class OfficeLocationController extends Controller
{
    public function __construct(
        private OfficeLocationService $service,
        private CompanyService $companyService
    ) {}

    public function index()
    {
        $locations = $this->service->getAll();
        $companies = $this->companyService->getAll();
        return view('master.office-locations.index', compact('locations', 'companies'));
    }

    public function show($id)
    {
        $location = $this->service->getById($id);
        return response()->json($location);
    }



    public function create()
    {
        $companies = $this->companyService->getAll();
        return view('master.office-locations.form', compact('companies'));
    }


    public function edit($id)
    {
        $location = $this->service->getById($id);
        $companies = $this->companyService->getAll();
        return view('master.office-locations.form', compact('location', 'companies'));
    }

    public function store(OfficeLocationRequest $request)
    {
        $data = $request->validated();
        $this->service->create(OfficeLocationDTO::from($data));
        return redirect()->route('office-locations.index')->with('success', 'Lokasi kantor berhasil disimpan.');
    }

    public function update(OfficeLocationRequest $request, $id)
    {
        $data = $request->validated();
        $this->service->update($id, OfficeLocationDTO::from($data));
        return redirect()->back()->with('success', 'Lokasi kantor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return redirect()->route('office-locations.index')->with('success', 'Lokasi kantor berhasil dihapus.');
    }
}
