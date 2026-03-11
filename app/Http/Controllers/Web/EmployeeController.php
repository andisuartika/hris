<?php

namespace App\Http\Controllers\Web;


use App\DTO\Employee\EmployeeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\Position;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->employee->company_id ?? 1;

        // 1. Kumpulkan semua request filter ke dalam array
        $filters = [
            'location_id' => $request->location_id,
            'status'      => $request->status,
        ];

        // 2. Ambil data pegawai lewat Service
        $employees = $this->employeeService->getAllEmployeesByCompany($companyId, $filters);

        // 3. Ambil data untuk dropdown filter
        $officeLocations = OfficeLocation::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        $positions = Position::where('company_id', $companyId)->get();

        return view('pages.employees.index', compact('employees', 'officeLocations', 'departments', 'positions'));
    }


    public function show($id)
    {
        $employee = $this->employeeService->getEmployeeDetails($id);
        $officeLocations = OfficeLocation::where('company_id', $employee->company_id)->get();
        return view('employees.detail', compact('employee', 'officeLocations'));
    }

    public function create()
    {
        $companyId = auth()->user()->employee->company_id ?? 1;
        $officeLocations = OfficeLocation::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        $positions = Position::where('company_id', $companyId)->get();

        return view('pages.employees.form', compact('officeLocations', 'departments', 'positions'));
    }

    public function store(StoreEmployeeRequest $request)
    {

        // Tambahkan status ke parameter DTO saat membuat instance class barunya
        $primaryLocationId = !empty($request->office_locations) ? $request->office_locations[0] : null;
        $dto = new EmployeeDTO(
            employeeCode: $request->employee_code,
            fullName: $request->full_name,
            email: $request->email,
            phone: $request->phone,
            address: $request->address,
            password: $request->password,
            officeLocationId: $primaryLocationId,
            companyId: auth()->user()->employee->company_id ?? 1,
            status: $request->status,
            departmentId: $request->department_id,
            positionId: $request->position_id,
            joinDate: $request->join_date,
            contractStart: $request->contract_start,
            contractEnd: $request->contract_end,
            officeLocations: $request->office_locations,
        );
        $this->employeeService->createEmployee($dto);

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $employee = $this->employeeService->getEmployeeById($id);

        $companyId = auth()->user()->employee->company_id ?? 1;
        $officeLocations = OfficeLocation::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();
        $positions = Position::where('company_id', $companyId)->get();

        return view('pages.employees.form', compact('employee', 'officeLocations', 'departments', 'positions'));
    }

    public function update(Request $request, $id)
    {
        $employee = $this->employeeService->getEmployeeById($id);

        $request->validate([
            // Abaikan validasi unique jika value-nya adalah milik user itu sendiri
            'employee_code' => 'required|unique:employees,employee_code,' . $id,
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|min:6',
            'office_location_id' => 'nullable|exists:office_locations,id',
            'status' => 'required|in:active,inactive'
        ]);

        $primaryLocationId = !empty($request->office_locations) ? $request->office_locations[0] : null;
        $dto = new EmployeeDTO(
            employeeCode: $request->employee_code,
            fullName: $request->full_name,
            email: $request->email,
            phone: $request->phone,
            address: $request->address,
            password: $request->password,
            officeLocationId: $primaryLocationId,
            companyId: $employee->company_id,
            status: $request->status,
            departmentId: $request->department_id,
            positionId: $request->position_id,
            joinDate: $request->join_date,
            contractStart: $request->contract_start,
            contractEnd: $request->contract_end,
            officeLocations: $request->office_locations
        );

        $this->employeeService->updateEmployee($id, $dto);

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->employeeService->deleteEmployee($id);

        return redirect()->route('employees.index')->with('success', 'Data pegawai berhasil dihapus.');
    }
}
