@extends('layouts.vertical', ['title' => 'Daftar Jabatan', 'subTitle' => 'Data Master'])

{{-- CSS Choices dihapus jika tidak digunakan lagi --}}

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
                                        <input type="search" id="position-search" class="form-control" placeholder="Cari Jabatan..." autocomplete="off">
                                        <iconify-icon icon="solar:magnifer-broken" class="search-widget-icon"></iconify-icon>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="text-dark fw-medium mb-0">{{ $positions->count() }} <span class="text-muted"> Jabatan</span></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-success me-1" data-bs-toggle="modal" data-bs-target="#modalPosition" onclick="addPosition()">
                                <i class="ri-add-line"></i> Tambah Jabatan
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
                    <h4 class="card-title">Semua Daftar Jabatan / Posisi</h4>
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
                                <th>Nama Jabatan</th>
                                <th>Instansi / Perusahaan</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($positions as $position)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="customCheck{{ $position->id }}">
                                        <label class="form-check-label" for="customCheck{{ $position->id }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm d-flex align-items-center justify-content-center bg-soft-warning text-warning rounded-circle fw-bold">
                                            <iconify-icon icon="solar:user-speak-rounded-broken" class="fs-20"></iconify-icon>
                                        </div>
                                        <div>
                                            <a href="#!" class="text-dark fw-medium fs-15">{{ $position->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border py-1 px-2">
                                        <i class="ri-building-line me-1 text-primary"></i> {{ $position->company->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $position->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button"
                                            class="btn btn-soft-primary btn-sm"
                                            onclick="editPosition('{{ $position->id }}', '{{ $position->name }}', '{{ $position->company_id }}')"
                                            data-bs-toggle="tooltip"
                                            title="Edit">
                                            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                        </button>

                                        <form action="{{ route('positions.destroy', $position->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm swal-confirm"
                                                data-title="Hapus Jabatan?"
                                                data-text="Data '{{ $position->name }}' akan dihapus!"
                                                data-confirm="Ya, Hapus!">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data jabatan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Form Position --}}
<div class="modal fade" id="modalPosition" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalPositionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="positionForm" method="POST" action="{{ route('positions.store') }}">
                @csrf
                <div id="methodField"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="modalPositionLabel">Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Instansi / Perusahaan <span class="text-danger">*</span></label>
                        {{-- ID diganti menjadi company_id agar lebih jelas --}}
                        <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                            <option value="">Pilih Instansi...</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Staff" required>
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
        // Tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Searching
        const searchInput = document.getElementById('position-search');
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                if (row.cells.length < 2) return;
                const posName = row.cells[1].textContent.toLowerCase();
                const compName = row.cells[2].textContent.toLowerCase();
                row.style.display = (posName.includes(query) || compName.includes(query)) ? "" : "none";
            });
        });
    });

    function editPosition(id, name, company_id) {
        const modalTitle = document.getElementById('modalPositionLabel');
        const form = document.getElementById('positionForm');
        const methodField = document.getElementById('methodField');

        modalTitle.innerText = "Edit Jabatan";
        form.action = `/positions/${id}`;
        methodField.innerHTML = `@method('PUT')`;

        // Set Values
        document.getElementById('name').value = name;
        document.getElementById('company_id').value = company_id; // Mengarah ke ID yang benar

        const modal = new bootstrap.Modal(document.getElementById('modalPosition'));
        modal.show();
    }

    function addPosition() {
        document.getElementById('modalPositionLabel').innerText = "Tambah Jabatan";
        document.getElementById('positionForm').action = "{{ route('positions.store') }}";
        document.getElementById('methodField').innerHTML = "";
        document.getElementById('positionForm').reset();
    }
</script>
@endsection
