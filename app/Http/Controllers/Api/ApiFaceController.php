<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Face Recognition
 *
 * Face template registration, status checking, and device sync.
 */
class ApiFaceController extends Controller
{
    use ApiResponse;

    /**
     * Register or update face template
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'face_embedding' => 'required|array',
            'face_embedding.*' => 'numeric',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('face/templates', 'local');
            }

            $template = $employee->faceTemplate()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'template_data' => $data['face_embedding'],
                    'image_path' => $photoPath,
                ]
            );

            return $this->success($template, 'Wajah berhasil didaftarkan');
        } catch (\Exception $e) {
            return $this->error(
                errors: $e->getMessage(),
                message: 'Gagal mendaftarkan wajah',
                status: 500
            );
        }
    }

    /**
     * Check if face template exists
     */
    public function template(Request $request)
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->error(
                errors: 'Akun tidak memiliki data pegawai.',
                message: 'Data pegawai tidak ditemukan',
                status: 404
            );
        }

        $exists = $employee->faceTemplate()->exists();

        return $this->success([
            'registered' => $exists,
        ], 'Status pendaftaran wajah');
    }

    /**
     * Sync face templates to mobile device
     *
     * Returns list of employees with face templates updated since specified timestamp.
     * Mobile app calls this periodically to sync face database for offline verification.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'updated_since' => 'nullable|integer',
        ]);

        $since = $request->integer('updated_since') ? \Carbon\Carbon::createFromTimestampMs($request->integer('updated_since')) : null;

        try {
            $query = \App\Models\EmployeeFaceTemplate::whereNotNull('template_data');

            if ($since) {
                $query->where('updated_at', '>', $since);
            }

            $templates = $query->with('employee')
                ->get()
                ->map(fn ($t) => [
                    'employee_id' => $t->employee_id,
                    'name' => $t->employee->name,
                    'embedding' => $t->template_data,
                    'updated_at' => $t->updated_at->getTimestampMs(),
                ]);

            return $this->success([
                'templates' => $templates,
                'sync_at' => now()->getTimestampMs(),
            ], 'Face database sync');
        } catch (\Exception $e) {
            return $this->error(
                errors: $e->getMessage(),
                message: 'Gagal sinkronisasi wajah',
                status: 500
            );
        }
    }
}
