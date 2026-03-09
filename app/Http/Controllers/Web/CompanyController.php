<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\DTO\Company\CompanyDTO;
use App\Http\Requests\Master\StoreCompanyRequest;
use App\Services\Master\CompanyService;

class CompanyController extends Controller
{
    // Inject Service melalui Constructor
    public function __construct(
        protected CompanyService $companyService
    ) {}

    /**
     * Menampilkan daftar perusahaan (jika ke depan ada banyak instansi)
     */
    public function index()
    {
        $companies = $this->companyService->getAll();
        return view('master.companies.index', compact('companies'));
    }

    /**
     * Form tambah perusahaan
     */
    public function create()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        return view('master.companies.form', compact('timezones'));
    }

    /**
     * Simpan data menggunakan DTO dan Service
     */
    public function store(StoreCompanyRequest $request)
    {
        // 1. Ubah request menjadi DTO
        $dto = CompanyDTO::fromRequest($request);

        // 2. Eksekusi logika bisnis di Service
        $this->companyService->createCompany($dto);

        return redirect()->route('companies.index')
            ->with('success', 'Data instansi berhasil ditambahkan!');
    }

    /**
     * Form edit perusahaan
     */
    public function edit(Company $company)
    {
        $timezones = \DateTimeZone::listIdentifiers();
        return view('master.companies.form', compact('company', 'timezones'));
    }

    /**
     * Update data
     */
    public function update(StoreCompanyRequest $request, Company $company)
    {
        $dto = CompanyDTO::fromRequest($request);
        $this->companyService->updateCompany($company, $dto);

        return redirect()->route('companies.index')
            ->with('success', 'Data instansi berhasil diperbarui!');
    }

    /**
     * Hapus data
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')
            ->with('success', 'Data instansi berhasil dihapus!');
    }
}
