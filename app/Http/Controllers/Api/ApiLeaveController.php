<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Services\Leave\LeaveService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Cuti & Izin
 *
 * Leave request management, balance tracking, dan approval workflow.
 */
class ApiLeaveController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaveService $leaveService
    ) {}

    /**
     * Get available leave types
     */
    public function types()
    {
        $types = LeaveType::where('is_active', true)
            ->select('id', 'name', 'max_days_per_year', 'requires_attachment')
            ->get();

        return $this->success($types, 'Jenis cuti tersedia');
    }

    /**
     * Get leave balance for current user
     */
    public function balance(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        $balance = $this->leaveService->balance($employee);

        return $this->success($balance, 'Saldo cuti');
    }

    /**
     * Get leave history
     *
     * Filters: status (pending/approved/rejected), year, month
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        $query = Leave::where('employee_id', $employee->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('year')) {
            $year = $request->integer('year');
            $query->whereYear('start_date', $year);
        }

        if ($request->filled('month')) {
            $month = $request->integer('month');
            $query->whereMonth('start_date', $month);
        }

        $leaves = $query->latest()->paginate(10);

        return $this->paginated($leaves);
    }

    /**
     * Create leave request
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $employee = $request->user()->employee;

            if (! $employee) {
                return $this->error(
                    errors: 'Akun tidak memiliki data pegawai.',
                    message: 'Data pegawai tidak ditemukan',
                    status: 404
                );
            }

            $leave = $this->leaveService->create($employee, $data, $request->file('attachment'));

            return $this->success(
                new LeaveResource($leave),
                'Pengajuan cuti dikirim'
            );
        } catch (\Exception $e) {
            return $this->error(
                errors: $e->getMessage(),
                message: 'Gagal membuat pengajuan cuti',
                status: 422
            );
        }
    }

    /**
     * Cancel leave request
     *
     * Only pending requests can be cancelled.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $employee = $request->user()->employee;

            if (! $employee) {
                return $this->error(
                    errors: 'Akun tidak memiliki data pegawai.',
                    message: 'Data pegawai tidak ditemukan',
                    status: 404
                );
            }

            $leave = Leave::where('id', $id)
                ->where('employee_id', $employee->id)
                ->firstOrFail();

            $this->leaveService->cancel($leave);

            return $this->success(
                new LeaveResource($leave),
                'Pengajuan cuti dibatalkan'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->error(
                errors: 'Pengajuan cuti tidak ditemukan',
                message: 'Not found',
                status: 404
            );
        } catch (\Exception $e) {
            return $this->error(
                errors: $e->getMessage(),
                message: 'Gagal membatalkan pengajuan cuti',
                status: 422
            );
        }
    }
}
