@extends('layouts.vertical', ['title' => 'Daftar Departemen', 'subTitle' => 'Data Master'])

@section('css')
@vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection

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
                                        <input type="search" id="department-search" class="form-control" placeholder="Cari Departemen..." autocomplete="off">
                                        <iconify-icon icon="solar:magnifer-broken" class="search-widget-icon"></iconify-icon>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="text-dark fw-medium mb-0">{{ $departments->count() }} <span class="text-muted"> Departemen</span></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalDepartment" onclick="addDepartment()">
                                <i class="ri-add-line"></i> Tambah Departemen
                            </button>
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
                    <h4 class="card-title">Semua Daftar Departemen / Divisi</h4>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle text-nowrap table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th style="width: 20px;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                        <label class="form-check-label" for="checkAll"></label>
                                    </div>
                                </th>
                                <th>Nama Departemen</th>
                                <th>Instansi / Perusahaan</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck{{ $department->id }}">
                                        <label class="form-check-label" for="customCheck{{ $department->id }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm d-flex align-items-center justify-content-center bg-soft-info text-info rounded-circle fw-bold">
                                            <iconify-icon icon="solar:structure-broken" class="fs-20"></iconify-icon>
                                        </div>
                                        <div>
                                            <a href="#!" class="text-dark fw-medium fs-15">{{ $department->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- Pastikan relasi 'company' sudah ada di model Department --}}
                                    <span class="badge bg-light text-dark border py-1 px-2">
                                        <i class="ri-building-line me-1 text-primary"></i> {{ $department->company->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $department->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button"
                                            class="btn btn-soft-primary btn-sm"
                                            onclick="editDepartment('{{ $department->id }}', '{{ $department->name }}')"
                                            data-bs-toggle="tooltip"
                                            title="Edit">
                                            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                        </button>

                                        {{-- Form Hapus (Tetap sama) --}}
                                        <form action="{{ route('departments.destroy', $department->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm swal-confirm"
                                                data-title="Hapus Departemen?"
                                                data-text="Data '{{ $department->name }}' akan dihapus!"
                                                data-confirm="Ya, Hapus!">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data departemen.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    {{-- {{ $departments->links() }} --}}
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modalDepartment" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="departmentForm" method="POST" action="{{ route('departments.store') }}">
                @csrf
                <div id="methodField"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDepartmentLabel">Tambah Departemen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Dropdown Pilih Instansi --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Instansi / Perusahaan <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-control @error('company_id') is-invalid @enderror" data-choices id="choices-single-default">
                            <option value="">Pilih Instansi...</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $department->company_id ?? '') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: IT Support" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tooltip initialization
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Search functionality
        const searchInput = document.getElementById('department-search');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();

            tableRows.forEach(row => {
                const deptName = row.cells[1].textContent.toLowerCase();
                const companyName = row.cells[2].textContent.toLowerCase();

                if (deptName.includes(query) || companyName.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
</script>

<script>
    function editDepartment(id, name) {
        // 1. Ambil elemen-elemen modal
        const modalElement = document.getElementById('modalDepartment');
        const form = document.getElementById('departmentForm');
        const modalTitle = document.getElementById('modalDepartmentLabel');
        const methodField = document.getElementById('methodField');
        const nameInput = document.getElementById('name');

        // 2. Ubah UI Modal menjadi mode Edit
        modalTitle.innerText = "Edit Departemen";

        // 3. Set URL Action ke route update (Laravel route: departments.update)
        form.action = `/departments/${id}`;

        // 4. Tambahkan Method PUT (karena HTML Form cuma support GET/POST)
        methodField.innerHTML = `@method('PUT')`;

        // 5. Isi input nama dengan data yang ada
        nameInput.value = name;

        // 6. Munculkan Modal
        const bModal = new bootstrap.Modal(modalElement);
        bModal.show();
    }

    // Fungsi tambahan: Reset form saat klik "Tambah" agar data edit tidak nyangkut
    function addDepartment() {
        document.getElementById('modalDepartmentLabel').innerText = "Tambah Departemen";
        document.getElementById('departmentForm').action = "{{ route('departments.store') }}";
        document.getElementById('methodField').innerHTML = "";
        document.getElementById('departmentForm').reset();
    }
</script>
@endsection
