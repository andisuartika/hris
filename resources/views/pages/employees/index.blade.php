@extends('layouts.vertical', ['title' => 'Data Pegawai', 'subTitle' => 'Kepegawaian'])

@section('css')
<link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
@endsection

@section('content')


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0 pb-3">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-6">
                        <h5 class="text-dark fw-medium mb-0">{{ $employees->count() }} <span class="text-muted"> Total Pegawai</span></h5>
                    </div>
                    <div class="col-lg-6">
                        <div class="text-md-end mt-3 mt-md-0">
                            <a href="{{ route('employees.create') }}" class="btn btn-success me-1"><i class="ri-add-line"></i> Tambah Pegawai</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- BARIS FILTER MULTI-KOLOM --}}
                <div class="bg-light p-3 border-top border-bottom">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Cari Pegawai</label>
                            <input type="text" id="filter-search" class="form-control form-control-sm custom-filter" placeholder="Ketik nama/email/code...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Filter Lokasi (Utama)</label>
                            <select id="filter-location" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Lokasi</option>
                                @foreach($officeLocations as $loc)
                                <option value="{{ $loc->name }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Filter Departement</label>
                            <select id="filter-department" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Departement</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Filter Jabatan</label>
                            <select id="filter-position" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Jabatan</option>
                                @foreach($positions as $pos)
                                <option value="{{ $pos->name }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Filter Status</label>
                            <select id="filter-status" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-reset-filter" class="btn btn-sm btn-soft-danger w-100">
                                <i class="ri-refresh-line align-middle me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Container Grid.js --}}
                <div id="employee-grid"></div>
            </div>
        </div>
    </div>
</div>


@endsection
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ========================
        // DATA RAW
        // =========================
        const rawEmployeeData = [
            @foreach($employees as $employee) {
                profil: gridjs.html(`
                <div class="d-flex align-items-center gap-2">
                    <div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->full_name) }}&background=random"
                        class="avatar-sm rounded-circle">
                    </div>
                    <div>
                        <a href="#!" class="text-dark fw-medium fs-15 d-block">
                            {{ $employee->full_name }}
                        </a>
                        <span class="text-muted fs-12">
                            {{ $employee->user->email ?? '-' }}
                        </span>
                    </div>
                </div>
            `),

                code: '{{ $employee->employee_code }}',

                dept_pos: gridjs.html(`
                <span class="d-block fw-medium text-dark">
                    {{ $employee->department->name ?? 'Belum Diatur' }}
                </span>
                <span class="d-block text-muted fs-12">
                    {{ $employee->position->name ?? '-' }}
                </span>
            `),

                contact_html: '{{ $employee->phone ?? "-" }}',
                location: '{{ $employee->officeLocation->name ?? "Belum Diatur" }}',
                date: '{{ $employee->join_date ? $employee->join_date->format("d M Y") : "-" }}',

                status_html: gridjs.html(
                    '{{ $employee->status }}' === 'active' ?
                    '<span class="badge bg-success-subtle text-success py-1 px-2 fs-13">Aktif</span>' :
                    '<span class="badge bg-danger-subtle text-danger py-1 px-2 fs-13">Non-Aktif</span>'
                ),

                aksi: gridjs.html(`
               <div class="d-flex align-items-center gap-2">
                <a href="{{ route('employees.show', $employee->id) }}"
                    class="btn btn-light btn-sm d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px"
                    title="Lihat">
                    <iconify-icon icon="solar:eye-broken" class="fs-18"></iconify-icon>
                </a>

                <a href="{{ route('employees.edit', $employee->id) }}"
                    class="btn btn-soft-primary btn-sm d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px"
                    title="Edit">
                    <iconify-icon icon="solar:pen-2-broken" class="fs-18"></iconify-icon>
                </a>

                <form action="{{ route('employees.destroy', $employee->id) }}"
                    method="POST"
                    class="m-0">

                    @csrf
                    @method('DELETE')

                    <button type="button"
                        class="btn btn-soft-danger btn-sm d-flex align-items-center justify-content-center swal-confirm"
                        style="width:40px;height:40px"
                        data-title="Hapus Pegawai?"
                        data-text="Data pegawai {{ $employee->full_name }} akan dihapus"
                        data-confirm="Ya, Hapus"
                        title="Hapus">

                        <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="fs-18"></iconify-icon>

                    </button>

                </form>

            </div>
            `),

                // DATA RAW UNTUK FILTER
                name_raw: '{{ strtolower($employee->full_name) }}',
                email_raw: '{{ strtolower($employee->user->email ?? "") }}',
                phone_raw: '{{ strtolower($employee->phone ?? "") }}',
                code_raw: '{{ strtolower($employee->employee_code ?? "") }}',
                department_raw: '{{ strtolower($employee->department->name ?? "") }}',
                position_raw: '{{ strtolower($employee->position->name ?? "") }}',
                status_raw: '{{ $employee->status }}'
            },
            @endforeach
        ];


        // =========================
        // GRID INIT
        // =========================
        const grid = new gridjs.Grid({
            columns: [{
                    id: 'profil',
                    name: 'Profil Pegawai'
                },
                {
                    id: 'code',
                    name: 'Code'
                },
                {
                    id: 'dept_pos',
                    name: 'Departemen & Jabatan'
                },
                {
                    id: 'contact_html',
                    name: 'No. Handphone'
                },
                {
                    id: 'location',
                    name: 'Lokasi Utama'
                },
                {
                    id: 'date',
                    name: 'Tgl Gabung'
                },
                {
                    id: 'status_html',
                    name: 'Status'
                },
                {
                    id: 'aksi',
                    name: 'Aksi',
                    sort: false
                }
            ],

            data: rawEmployeeData,

            sort: true,

            pagination: {
                enabled: true,
                limit: 10
            },

            className: {
                table: 'table align-middle text-nowrap table-hover mb-0'
            },

            language: {
                pagination: {
                    previous: 'Sebelumnya',
                    next: 'Selanjutnya',
                    showing: 'Menampilkan',
                    results: () => 'Data'
                }
            }

        });

        grid.render(document.getElementById("employee-grid"));


        // =========================
        // FILTER LOGIC
        // =========================
        function applyFilters() {

            const fSearch = document.getElementById('filter-search').value.toLowerCase();
            const fLocation = document.getElementById('filter-location').value;
            const fDepartment = document.getElementById('filter-department').value.toLowerCase();
            const fPosition = document.getElementById('filter-position').value.toLowerCase();
            const fStatus = document.getElementById('filter-status').value;

            const filteredData = rawEmployeeData.filter(row => {

                const searchMatch =
                    row.name_raw.includes(fSearch) ||
                    row.email_raw.includes(fSearch) ||
                    row.phone_raw.includes(fSearch) ||
                    row.code_raw.includes(fSearch);

                const locationMatch =
                    fLocation === '' || row.location === fLocation;

                const departmentMatch =
                    fDepartment === '' || row.department_raw === fDepartment;

                const positionMatch =
                    fPosition === '' || row.position_raw === fPosition;

                const statusMatch =
                    fStatus === '' || row.status_raw === fStatus;

                return searchMatch && locationMatch && departmentMatch && positionMatch && statusMatch;

            });

            grid.updateConfig({
                data: filteredData
            }).forceRender();
        }


        // =========================
        // EVENT FILTER
        // =========================
        document.querySelectorAll('.custom-filter').forEach(el => {
            el.addEventListener('input', applyFilters);
            el.addEventListener('change', applyFilters);
        });


        // =========================
        // RESET FILTER
        // =========================
        document.getElementById('btn-reset-filter').addEventListener('click', function() {

            document.getElementById('filter-search').value = '';
            document.getElementById('filter-location').value = '';
            document.getElementById('filter-department').value = '';
            document.getElementById('filter-position').value = '';
            document.getElementById('filter-status').value = '';

            grid.updateConfig({
                data: rawEmployeeData
            }).forceRender();

        });

    });
</script>
