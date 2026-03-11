<?php

namespace App\Http\Controllers\Web;

use App\DTO\Master\PositionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StorePositionRequest;
use App\Services\Master\CompanyService;
use App\Services\Master\PositionService;
use Illuminate\Http\RedirectResponse;

class PositionController extends Controller
{
    public function __construct(
        private readonly PositionService $service,
        private readonly CompanyService $companyService
    ) {}

    public function index()
    {
        // Pastikan getAll() menghandle filter berdasarkan company_id user jika perlu
        $positions = $this->service->getAll();
        $companies = $this->companyService->getAll();

        return view('pages.master.positions.index', compact('positions', 'companies'));
    }

    public function store(StorePositionRequest $request)
    {
        // Kirim data divalidasi dan company_id ke DTO
        $dto = new PositionDTO(
            name: $request->name,
            company_id: $request->company_id,
        );

        $this->service->create($dto);
        return redirect()->route('positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update($id, StorePositionRequest $request)
    {
        $dto = new PositionDTO(
            name: $request->name,
            company_id: $request->company_id,
        );

        $this->service->update($id, $dto);
        return redirect()->route('positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $this->service->delete($id);
        return redirect()->route('positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
