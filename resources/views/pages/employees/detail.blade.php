
@extends('layouts.vertical', [
'title' => 'Detail Pegawai',
'titleRoute' => route('employees.index'),
'subTitle' => 'Data Pegawai',
'subTitleRoute' => route('employees.index'),
'parentTitle' => 'Kepegawaian',
'parentRoute' => '#',
])

@section('content')
<div class="row">
    <div class="col-xl-4 col-lg-5">
        {{-- KARTU PROFIL UTAMA --}}
        <div class="card text-center">
            <div class="card-body">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->full_name) }}&background=random&size=128" alt="img" class="rounded-circle avatar-xl img-thumbnail">
                <h4 class="mt-3 mb-1">{{ $employee->full_name }}</h4>
                <p class="text-muted">{{ $employee->position->name ?? '-' }} | {{ $employee->department->name ?? '-' }}</p>

                <div class="mt-3">
                    @if($employee->status === 'active')
                    <span class="badge bg-success-subtle text-success px-3 py-2 fs-13">Aktif</span>
                    @else
                    <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-13">Non-Aktif</span>
                    @endif
                </div>

                <div class="row mt-4 g-2">
                    <div class="col-6">
                        <a href="https://wa.me/{{ $employee->phone }}" target="_blank" class="btn btn-soft-success w-100">
                            <i class="ri-whatsapp-line me-1"></i> WhatsApp
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="mailto:{{ $employee->user->email }}" class="btn btn-soft-primary w-100">
                            <i class="ri-mail-line me-1"></i> Email
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFORMASI KONTAK & DASAR --}}
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">Informasi Pribadi</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-light rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-fingerprint-line fs-20 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 fs-13">NIP / Kode Pegawai</p>
                        <h5 class="mb-0">{{ $employee->employee_code }}</h5>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-light rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-phone-line fs-20 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 fs-13">Nomor Telepon</p>
                        <h5 class="mb-0">{{ $employee->phone ?? '-' }}</h5>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-light rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-map-pin-line fs-20 text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 fs-13">Alamat Domisili</p>
                        <h5 class="mb-0 fs-14">{{ $employee->address ?? 'Alamat belum diatur' }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        {{-- STATISTIK SINGKAT / SUMMARY KONTRAK --}}
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium mb-1">Tgl Bergabung</p>
                                <h4 class="mb-0">{{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d M Y') : '-' }}</h4>
                            </div>
                            <div class="avatar-md bg-primary-subtle rounded">
                                <iconify-icon icon="solar:calendar-date-bold" class="fs-32 text-primary avatar-title"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted fw-medium mb-1">Masa Kontrak</p>
                                <h4 class="mb-0">
                                    {{ $employee->contract_start ? \Carbon\Carbon::parse($employee->contract_start)->format('d M Y') : 'N/A' }}
                                    -
                                    {{ $employee->contract_end ? \Carbon\Carbon::parse($employee->contract_end)->format('d M Y') : 'Permanent' }}
                                </h4>
                            </div>
                            <div class="avatar-md bg-warning-subtle rounded">
                                <iconify-icon icon="solar:document-text-bold" class="fs-32 text-warning avatar-title"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL PENUGASAN LOKASI --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <h4 class="card-title">Lokasi Kerja & Penugasan</h4>
                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-soft-primary">Edit Lokasi</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $empLocations = is_array($employee->office_locations) ? $employee->office_locations : (json_decode($employee->office_locations, true) ?? []);
                    @endphp

                    @forelse($officeLocations->whereIn('id', $empLocations) as $loc)
                    <div class="col-md-6">
                        <div class="border rounded p-3 shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-light rounded">
                                    <iconify-icon icon="solar:home-bold-duotone" class="fs-24 text-primary avatar-title"></iconify-icon>
                                </div>
                                <div>
                                    <h5 class="mb-1">{{ $loc->name }}</h5>
                                    <p class="text-muted mb-0 fs-12">{{ $loc->address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-3">
                        <p class="text-muted">Belum ada lokasi penugasan khusus.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIWAYAT ABSENSI SINGKAT (TABEL) --}}
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">Log Aktivitas Terakhir</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Contoh Data Statis, nanti bisa loop dari Attendance --}}
                            <tr>
                                <td>{{ now()->format('d/m/Y') }}</td>
                                <td>08:00</td>
                                <td>17:05</td>
                                <td><span class="badge bg-success">Tepat Waktu</span></td>
                                <td><a href="#!" class="btn btn-sm btn-light">Detail</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
