<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Company\CompanyService;
use App\DTO\Company\CompanyDTO;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        protected CompanyService $companyService
    ) {}

    public function create()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        return view('companies.create', compact('timezones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'timezone' => 'required|string',
        ]);

        $dto = CompanyDTO::fromRequest($request);
        $this->companyService->createCompany($dto);

        return redirect()->route('companies.index')
            ->with('success', 'Perusahaan berhasil dibuat!');
    }


    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'timezone' => 'required|string',
        ]);

        $dto = CompanyDTO::fromRequest($request);
        $this->companyService->updateCompany($company, $dto);

        return redirect()->route('companies.index')
            ->with('success', 'Perusahaan berhasil diperbarui!');
    }
}
