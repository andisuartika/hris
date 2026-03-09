@extends('layouts.vertical', ['title' => isset($employee) ? 'Edit Pegawai' : 'Tambah Pegawai', 'subTitle' => 'Kepegawaian'])

@section('css')
@vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12 mx-auto">

        {{-- SATU FORM UTAMA UNTUK SEMUA CARD --}}
        <form action="{{ isset($employee) ? route('employees.update', $employee->id) : route('employees.store') }}" method="POST">
            @csrf
            @isset($employee)
            @method('PUT')
            @endisset

            {{-- 1. CARD INFORMASI KARYAWAN --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title">Informasi Karyawan</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label for="employee_code" class="form-label">NIP / Kode Pegawai <span class="text-danger">*</span></label>
                            <input type="text" id="employee_code" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror"
                                value="{{ old('employee_code', $employee->employee_code ?? '') }}" placeholder="Contoh: PEG-001" required>
                            @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" id="full_name" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                                value="{{ old('full_name', $employee->full_name ?? '') }}" placeholder="Masukkan nama lengkap sesuai KTP" required>
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-12">
                            <label for="address" class="form-label">Alamat Domisili</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                placeholder="Masukkan alamat lengkap">{{ old('address', $employee->address ?? '') }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. CARD INFORMASI AKUN --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title">Informasi Akun (Login)</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $employee->user->email ?? '') }}" placeholder="email@kominfo.go.id" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="phone" class="form-label">Nomor Handphone</label>
                            <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $employee->phone ?? '') }}" placeholder="08xxxxxxxxxx">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="password" class="form-label">
                                Kata Sandi
                                @isset($employee) <span class="text-muted fw-normal fs-12">(Kosongkan jika tidak diubah)</span> @else <span class="text-danger">*</span> @endisset
                            </label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Minimal 6 karakter" {{ !isset($employee) ? 'required' : '' }}>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                placeholder="Ketik ulang kata sandi" {{ !isset($employee) ? 'required' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. CARD INFORMASI PEKERJAAN --}}
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title">Informasi Pekerjaan</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- DROPDOWN DEPARTEMEN --}}
                        <div class="col-lg-6">
                            <label for="department_id" class="form-label">Departemen</label>
                            <select class="form-control @error('department_id') is-invalid @enderror" id="department_id" name="department_id" data-choices>
                                <option value="">Pilih Departemen</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- DROPDOWN JABATAN --}}
                        <div class="col-lg-6">
                            <label for="position_id" class="form-label">Jabatan</label>
                            <select class="form-control @error('position_id') is-invalid @enderror" id="position_id" name="position_id" data-choices>
                                <option value="">Pilih Jabatan</option>
                                @foreach($positions as $pos)
                                <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id ?? '') == $pos->id ? 'selected' : '' }}>
                                    {{ $pos->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-lg-4">
                            <label for="join_date" class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
                            {{-- Jika data join_date ada, format menjadi YYYY-MM-DD agar terbaca oleh input type="date" --}}
                            <input type="date" id="join_date" name="join_date" class="form-control @error('join_date') is-invalid @enderror"
                                value="{{ old('join_date', isset($employee->join_date) ? \Carbon\Carbon::parse($employee->join_date)->format('Y-m-d') : '') }}" required>
                            @error('join_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="contract_start" class="form-label">Mulai Kontrak <span class="text-muted fw-normal fs-12">(Opsional)</span></label>
                            <input type="date" id="contract_start" name="contract_start" class="form-control @error('contract_start') is-invalid @enderror"
                                value="{{ old('contract_start', isset($employee->contract_start) ? \Carbon\Carbon::parse($employee->contract_start)->format('Y-m-d') : '') }}">
                            @error('contract_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="contract_end" class="form-label">Berakhir Kontrak <span class="text-muted fw-normal fs-12">(Opsional)</span></label>
                            <input type="date" id="contract_end" name="contract_end" class="form-control @error('contract_end') is-invalid @enderror"
                                value="{{ old('contract_end', isset($employee->contract_end) ? \Carbon\Carbon::parse($employee->contract_end)->format('Y-m-d') : '') }}">
                            @error('contract_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-lg-6">
                            <label for="office_locations" class="form-label">Lokasi Kantor <span class="text-muted fw-normal fs-12">(Bisa pilih lebih dari satu)</span></label>

                            @php
                            // Ambil data lokasi lama jika ada (berupa array)
                            $selectedLocations = old('office_locations', isset($employee) && is_array($employee->office_locations) ? $employee->office_locations : []);
                            @endphp

                            <select class="form-control @error('office_locations') is-invalid @enderror" id="office_locations" name="office_locations[]" data-choices data-choices-removeItem multiple>
                                <option value="">Pilih Kantor</option>
                                @foreach($officeLocations as $location)
                                <option value="{{ $location->id }}" {{ in_array($location->id, $selectedLocations) ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('office_locations') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-lg-6">
                            <label for="status" class="form-label">Status Pegawai <span class="text-danger">*</span></label>
                            <select class="form-control" data-choices name="status" id="status" required>
                                <option value="active" {{ old('status', $employee->status ?? 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $employee->status ?? '') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOMBOL AKSI --}}
            <div class="mb-4 rounded">
                <div class="row justify-content-end g-2">
                    <div class="col-lg-2">
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger w-100">Batal</a>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">{{ isset($employee) ? 'Simpan Perubahan' : 'Simpan Pegawai' }}</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection 
