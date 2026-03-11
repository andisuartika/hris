@extends('layouts.vertical', ['title' => 'Roles & Permissions', 'subTitle' => 'Sistem'])

@section('css')
<link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h4 class="card-title">Roles & Access Control</h4>
                    <p class="text-muted mb-0 fs-13">Kelola grup pengguna dan batasan hak akses modul.</p>
                </div>
                <button onclick="addRole()" class="btn btn-sm btn-primary">
                    <i class="ri-shield-user-line me-1"></i> Tambah Role
                </button>
            </div>

            <div class="card-body p-0">
                {{-- Container Grid.js --}}
                <div id="role-grid"></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ROLE & PERMISSION (ALL-IN-ONE) --}}
<div class="modal fade" id="modalRole" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0">
            <form id="roleForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">Konfigurasi Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="r_name" class="form-control" placeholder="Contoh: Manager HR, Admin Cabang" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="ri-lock-2-line me-1"></i> Daftar Permissions</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label fw-medium" for="checkAll">Pilih Semua</label>
                        </div>
                    </div>

                    <div class="row g-3">
                        @php
                            $labels = [
                                'dashboard'  => 'Dashboard',
                                'master'     => 'Data Master',
                                'pegawai'    => 'Kepegawaian',
                                'absensi'    => 'Presensi/Absensi',
                                'cuti'       => 'Cuti & Izin',
                                'payroll'    => 'Payroll',
                                'pengumuman' => 'Pengumuman',
                                'setting'    => 'Sistem Setting',
                            ];
                        @endphp

                        @foreach($permissions as $prefix => $groupPerms)
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="perm-card h-100">
                                <div class="perm-header">
                                    {{ $labels[$prefix] ?? ucfirst($prefix) }}
                                </div>
                                <div class="p-2">
                                    @foreach($groupPerms as $perm)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input perm-check" type="checkbox" name="permissions[]"
                                               value="{{ $perm->name }}" id="p_{{ $perm->id }}">
                                        <label class="form-check-label fs-13" for="p_{{ $perm->id }}">
                                            {{ ucwords(str_replace(['-', ':'], [' ', ' '], explode(':', $perm->name)[1] ?? $perm->name)) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalRole = new bootstrap.Modal(document.getElementById('modalRole'));

        // ========================
        // DATA UNTUK GRID.JS
        // =========================
        const roleData = [
            @foreach($roles as $role) {
                role_info: gridjs.html(`
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm d-flex align-items-center justify-content-center rounded-circle bg-soft-primary text-primary fw-bold fs-14">
                            {{ strtoupper(substr($role->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="text-dark fw-medium fs-15 text-uppercase">{{ $role->name }}</span>
                            <br><small class="text-muted">Guard: {{ $role->guard_name }}</small>
                        </div>
                    </div>
                `),
                total: gridjs.html(`<span class="badge bg-primary-subtle text-primary py-1 px-2 fs-12">{{ $role->permissions->count() }} Izin Terpasang</span>`),
                aksi: gridjs.html(`
                    <div class="d-flex gap-2">
                        <button onclick='editRole(@json($role), @json($role->permissions->pluck("name")))' class="btn btn-soft-primary btn-sm">
                            <iconify-icon icon="solar:settings-bold-duotone" class="align-middle fs-18"></iconify-icon> Akses
                        </button>

                        @if($role->name !== 'admin')
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline m-0">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-soft-danger btn-sm swal-confirm">
                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                            </button>
                        </form>
                        @endif
                    </div>
                `)
            },
            @endforeach
        ];

        // RENDER GRID
        new gridjs.Grid({
            columns: [
                { id: 'role_info', name: 'Role & Guard' },
                { id: 'total', name: 'Permissions' },
                { id: 'aksi', name: 'Opsi', sort: false }
            ],
            data: roleData,
            search: true,
            sort: true,
            pagination: { enabled: true, limit: 10 },
            className: { table: 'table mb-0' }
        }).render(document.getElementById("role-grid"));

        // =========================
        // LOGIC MODAL & CHECKBOX
        // =========================
        window.addRole = function() {
            document.getElementById('modalTitle').innerText = "Buat Role Baru";
            document.getElementById('roleForm').action = "{{ route('roles.store') }}";
            document.getElementById('methodField').innerHTML = "";
            document.getElementById('roleForm').reset();
            document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
            modalRole.show();
        };

        window.editRole = function(role, perms) {
            document.getElementById('modalTitle').innerText = "Edit Akses: " + role.name.toUpperCase();
            document.getElementById('roleForm').action = "/roles/" + role.id;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('r_name').value = role.name;

            document.querySelectorAll('.perm-check').forEach(cb => {
                cb.checked = perms.includes(cb.value);
            });
            modalRole.show();
        };

        // Check All Feature
        document.getElementById('checkAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.perm-check').forEach(cb => cb.checked = isChecked);
        });
    });
</script>
@endsection
