<?php

namespace App\Http\Controllers\Web;

use App\DTO\Master\DepartmentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreDepartementRequest;
use App\Services\Master\CompanyService;
use App\Services\Master\DepartmentService;

class DepartementController extends Controller
{
    public function __construct(
        private readonly DepartmentService $service,
        private readonly CompanyService $companyService
    ) {}

    public function index()
    {
        $departments = $this->service->getAll();
        $companies = $this->companyService->getAll();
        return view('pages.master.departments.index', compact('departments', 'companies'));
    }


    public function store(StoreDepartementRequest $request)
    {
        // Cukup instansiasi DTO sekali saja
        $dto = new DepartmentDTO(
            name: $request->validated('name'),
            company_id: $request->validated('company_id'),
        );

        $this->service->create($dto);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil ditambah');
    }

    public function update(StoreDepartementRequest $request, $id)
    {
        $dto = DepartmentDTO::fromRequest($request);
        $this->service->update($id, $dto);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil diperbarui');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return redirect()->route('departments.index')->with('success', 'Departemen berhasil dihapus');
    }
}
