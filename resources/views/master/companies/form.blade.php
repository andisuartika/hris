@extends('layouts.vertical', ['title' => isset($company) ? 'Edit Instansi' : 'Tambah Instansi', 'subTitle' => 'Data Master'])
@section('css')
@vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection


@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">{{ isset($company) ? 'Edit' : 'Daftarkan' }} Instansi</h4>
            </div>
            <div class="card-body">
                <form action="{{ isset($company) ? route('companies.update', $company->id) : route('companies.store') }}" method="POST">
                    @csrf
                    @if(isset($company)) @method('PUT') @endif

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Instansi / Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $company->name ?? '') }}" placeholder="Masukkan nama resmi instansi" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zona Waktu (Timezone) <span class="text-danger">*</span></label>
                            <select name="timezone" class="form-control" data-choices id="choices-single-default">
                                @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $company->timezone ?? 'Asia/Jakarta') == $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Gunakan Asia/Jakarta (WIB), Asia/Makassar (WITA), atau Asia/Jayapura (WIT).</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('companies.index') }}" class="btn btn-light">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
