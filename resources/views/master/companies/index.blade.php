@extends('layouts.vertical', ['title' => 'Daftar Instansi', 'subTitle' => 'Data Master'])

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row justify-content-between">
                    <div class="col-lg-6">
                        <div class="row align-items-center">
                            <div class="col-lg-6">
                                <form class="app-search d-none d-md-block me-auto">
                                    <div class="position-relative">
                                        <input type="search" id="company-search" class="form-control" placeholder="Cari Instansi..." autocomplete="off">
                                        <iconify-icon icon="solar:magnifer-broken" class="search-widget-icon"></iconify-icon>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="text-dark fw-medium mb-0">{{ $companies->count() }} <span class="text-muted"> Instansi</span></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-md-end mt-3 mt-md-0">
                            <a href="{{ route('companies.create') }}" class="btn btn-success me-1"><i class="ri-add-line"></i> Tambah Instansi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h4 class="card-title">Semua Daftar Instansi</h4>
                </div>

            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle text-nowrap table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 20px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck1">
                                        <label class="form-check-label" for="customCheck1"></label>
                                    </div>
                                </th>
                                <th>Nama Instansi</th>
                                <th>Zona Waktu (Timezone)</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companies as $company)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck{{ $company->id }}">
                                        <label class="form-check-label" for="customCheck{{ $company->id }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            {{-- Menampilkan inisial perusahaan sebagai pengganti foto --}}
                                            <div class="avatar-sm d-flex align-items-center justify-content-center bg-soft-primary text-primary rounded-circle fw-bold">
                                                {{ strtoupper(substr($company->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <a href="#!" class="text-dark fw-medium fs-15">{{ $company->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted"><i class="ri-time-line me-1"></i>{{ $company->timezone }}</span>
                                </td>
                                <td>{{ $company->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-soft-primary btn-sm">
                                            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        {{-- Form Hapus dengan Trigger Swal --}}
                                        <form action="{{ route('companies.destroy', $company->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-soft-danger btn-sm swal-confirm"
                                                data-title="Hapus Instansi?"
                                                data-text="Menghapus '{{ $company->name }}' akan berdampak pada data pegawai terkait!"
                                                data-confirm="Ya, Hapus!">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data instansi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{-- Pagination Laravel --}}
                <div class="d-flex justify-content-end">
                    {{-- $companies->links() --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // Inisialisasi tooltip jika diperlukan
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[type="search"]');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();

            tableRows.forEach(row => {
                // Kita ambil teks dari kolom Nama (index 1) dan Timezone (index 2)
                const name = row.cells[1].textContent.toLowerCase();
                const timezone = row.cells[2].textContent.toLowerCase();

                if (name.includes(query) || timezone.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
</script>

@endsection
