@extends('layouts.vertical', ['title' => 'Manajemen Pengguna', 'subTitle' => 'Kepegawaian'])

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
                        <h5 class="text-dark fw-medium mb-0">{{ $users->count() }} <span class="text-muted"> Total Pengguna</span></h5>
                    </div>
                    <div class="col-lg-6 text-md-end mt-3 mt-md-0">
                        <button class="btn btn-success" onclick="addUser()"><i class="ri-add-line"></i> Tambah Pengguna</button>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- FILTER BAR --}}
                <div class="bg-light p-3 border-top border-bottom">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fs-12 text-muted mb-1">Cari Pengguna</label>
                            <input type="text" id="filter-search" class="form-control form-control-sm custom-filter" placeholder="Nama/Email/Phone...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Role</label>
                            <select id="filter-role" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                <option value="{{ strtolower($role->name) }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-12 text-muted mb-1">Status</label>
                            <select id="filter-status" class="form-select form-select-sm custom-filter">
                                <option value="">Semua Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btn-reset-filter" class="btn btn-sm btn-soft-danger w-100">
                                <i class="ri-refresh-line align-middle me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div id="user-grid"></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL USER --}}
<div class="modal fade" id="modalUser" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <form id="userForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="u_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="u_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" id="u_phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" id="u_role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Akun</label>
                            <select name="status" id="u_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <hr class="my-2 text-muted">

                        <div class="col-md-6 mb-3">
                            <label class="form-label" id="label_pass">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="u_password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" id="label_conf">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="u_password_conf" class="form-control">
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-none" id="passNote">Biarkan kosong jika tidak ingin mengubah password.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
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
        const modalEl = new bootstrap.Modal(document.getElementById('modalUser'));

        // ========================
        // DATA RAW DARI BLADE (SPATIE COMPATIBLE)
        // =========================
        const rawUserData = [
            @foreach($users as $user) {
                @php $userRole = $user->getRoleNames()->first(); @endphp

                name_card: gridjs.html(`
                    <div class="d-flex align-items-center gap-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" class="avatar-sm rounded-circle">
                        <div>
                            <span class="fw-medium text-dark d-block">${@json($user->name)}</span>
                            <small class="text-muted">${@json($user->email)}</small>
                        </div>
                    </div>
                `),
                phone: @json($user->phone ?? "-"),
                role_display: gridjs.html(`<span class="badge bg-soft-primary text-primary px-2 py-1">{{ $userRole ?? 'No Role' }}</span>`),
                status_display: gridjs.html(`
                    <span class="badge {{ $user->status === 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} py-1 px-2 fs-12">
                        {{ ucfirst($user->status) }}
                    </span>
                `),
                aksi: gridjs.html(`
                    <div class="d-flex gap-1">
                        <button onclick='editUser(@json($user), "{{ $userRole }}")' class="btn btn-soft-primary btn-sm d-flex align-items-center justify-content-center" style="width:32px;height:32px">
                            <iconify-icon icon="solar:pen-2-broken" class="fs-16"></iconify-icon>
                        </button>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline m-0">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-soft-danger btn-sm d-flex align-items-center justify-content-center swal-confirm" style="width:32px;height:32px">
                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="fs-16"></iconify-icon>
                            </button>
                        </form>
                    </div>
                `),
                // Filter Raw
                name_raw: @json(strtolower($user->name)),
                email_raw: @json(strtolower($user->email)),
                phone_raw: @json(strtolower($user->phone ?? "")),
                role_raw: @json(strtolower($userRole ?? "")),
                status_raw: @json($user->status)
            },
            @endforeach
        ];

        // =========================
        // GRID.JS INIT
        // =========================
        const grid = new gridjs.Grid({
            columns: [
                { id: 'name_card', name: 'User Profile' },
                { id: 'phone', name: 'Phone' },
                { id: 'role_display', name: 'Role' },
                { id: 'status_display', name: 'Status' },
                { id: 'aksi', name: 'Aksi', sort: false }
            ],
            data: rawUserData,
            sort: true,
            pagination: { enabled: true, limit: 10 },
            className: { table: 'table align-middle text-nowrap table-hover mb-0' }
        }).render(document.getElementById("user-grid"));

        // =========================
        // FILTER LOGIC
        // =========================
        function applyFilters() {
            const fSearch = document.getElementById('filter-search').value.toLowerCase();
            const fStatus = document.getElementById('filter-status').value;
            const fRole = document.getElementById('filter-role').value.toLowerCase();

            const filtered = rawUserData.filter(row => {
                const sMatch = row.name_raw.includes(fSearch) || row.email_raw.includes(fSearch) || row.phone_raw.includes(fSearch);
                const stMatch = fStatus === '' || row.status_raw === fStatus;
                const rMatch = fRole === '' || row.role_raw === fRole;
                return sMatch && stMatch && rMatch;
            });
            grid.updateConfig({ data: filtered }).forceRender();
        }

        document.querySelectorAll('.custom-filter').forEach(el => el.addEventListener('input', applyFilters));
        document.getElementById('btn-reset-filter').addEventListener('click', () => {
            document.querySelectorAll('.custom-filter').forEach(el => el.value = '');
            grid.updateConfig({ data: rawUserData }).forceRender();
        });

        // =========================
        // MODAL FUNCTIONS
        // =========================
        window.addUser = function() {
            document.getElementById('modalTitle').innerText = "Tambah Pengguna Baru";
            document.getElementById('userForm').action = "{{ route('users.store') }}";
            document.getElementById('methodField').innerHTML = "";
            document.getElementById('userForm').reset();

            // Password logic for Create
            document.getElementById('u_password').required = true;
            document.getElementById('u_password_conf').required = true;
            document.getElementById('passNote').classList.add('d-none');
            document.getElementById('label_pass').innerHTML = 'Password <span class="text-danger">*</span>';
            document.getElementById('label_conf').innerHTML = 'Konfirmasi Password <span class="text-danger">*</span>';

            modalEl.show();
        }

        window.editUser = function(user, roleName) {
            document.getElementById('modalTitle').innerText = "Edit Pengguna";
            document.getElementById('userForm').action = `/users/${user.id}`;
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

            document.getElementById('u_name').value = user.name;
            document.getElementById('u_email').value = user.email;
            document.getElementById('u_phone').value = user.phone || '';
            document.getElementById('u_status').value = user.status;
            document.getElementById('u_role').value = roleName;

            // Password logic for Edit
            document.getElementById('u_password').required = false;
            document.getElementById('u_password_conf').required = false;
            document.getElementById('u_password').value = '';
            document.getElementById('u_password_conf').value = '';
            document.getElementById('passNote').classList.remove('d-none');
            document.getElementById('label_pass').innerText = 'Ganti Password';
            document.getElementById('label_conf').innerText = 'Konfirmasi Ganti Password';

            modalEl.show();
        }
    });
</script>
@endsection
