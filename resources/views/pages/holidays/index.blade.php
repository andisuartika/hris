@extends('layouts.vertical', ['title' => 'Holiday', 'subTitle' => 'Sistem'])

@section('css')

<link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />

<style>
    .gridjs-search {
        position: relative;
        margin-bottom: 15px;
    }

    .gridjs-search-input {
        padding-left: 38px !important;
        height: 40px;
        border-radius: 8px;
    }

    .gridjs-search::before {
        content: "\F52A";
        font-family: "remixicon";
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 16px;
    }
</style>

@endsection


@section('content')

<div class="row">
    <div class="col-xl-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center border-bottom">

                <div>
                    <h4 class="card-title">Holiday</h4>
                    <p class="text-muted mb-0 fs-13">
                        Kelola hari libur perusahaan dan nasional.
                    </p>
                </div>

                <div class="d-flex gap-2">

                    <button onclick="generateHoliday()" class="btn btn-sm btn-success">
                        <i class="ri-download-cloud-line me-1"></i> Generate Nasional
                    </button>

                    <button onclick="addHoliday()" class="btn btn-sm btn-primary">
                        <i class="ri-calendar-event-line me-1"></i> Tambah Libur
                    </button>

                </div>

            </div>

            <div class="card-body p-0">
                <div id="holiday-grid"></div>
            </div>

        </div>
    </div>
</div>


{{-- MODAL HOLIDAY --}}
<div class="modal fade" id="modalHoliday" data-bs-backdrop="static">

    <div class="modal-dialog">

        <div class="modal-content border-0">

            <form id="holidayForm" method="POST">

                @csrf
                <div id="methodField"></div>

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">Holiday</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nama Libur</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="holiday_date" id="holiday_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Libur</label>

                        <select name="type" id="type" class="form-control">

                            <option value="national">National</option>
                            <option value="religious">Religious</option>
                            <option value="company">Company</option>
                            <option value="special">Special</option>

                        </select>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary px-4">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- MODAL GENERATE HOLIDAY --}}
<div class="modal fade" id="modalGenerateHoliday">

    <div class="modal-dialog">

        <div class="modal-content">

            <form id="generateForm" method="POST">

                @csrf

                <div class="modal-header bg-light">
                    <h5 class="modal-title">Generate Hari Libur Nasional</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">Tahun</label>

                    <input
                        type="number"
                        id="year"
                        class="form-control"
                        value="{{ date('Y') }}"
                        required>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button class="btn btn-success">
                        Generate
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endsection


@section('script')

<script src="https://cdn.jsdelivr.net/npm/gridjs/dist/gridjs.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /* =============================
        MODAL
        ============================= */

        const modalHoliday = new bootstrap.Modal(
            document.getElementById('modalHoliday')
        );

        const modalGenerate = new bootstrap.Modal(
            document.getElementById('modalGenerateHoliday')
        );


        /* =============================
        GENERATE BUTTON
        ============================= */

        window.generateHoliday = function() {
            modalGenerate.show();
        };


        /* =============================
        GENERATE FORM
        ============================= */

        const generateForm = document.getElementById('generateForm');

        if (generateForm) {

            generateForm.addEventListener('submit', function(e) {

                e.preventDefault();

                const year = document.getElementById('year').value;

                this.action = "/holidays/generate/" + year;

                this.submit();

            });

        }


        /* =============================
        DATA
        ============================= */

        const holidays = @json($holidays);


        /* =============================
        GRID DATA
        ============================= */

        const data = holidays.map(holiday => ({

            name: holiday.name,

            date: holiday.holiday_date ?
                holiday.holiday_date.split('T')[0] : '',

            type: holiday.type,

            aksi: gridjs.html(`

<button onclick="editHoliday(${holiday.id})"
class="btn btn-soft-primary btn-sm">
Edit
</button>

<button onclick="deleteHoliday(${holiday.id})"
class="btn btn-soft-danger btn-sm">
Delete
</button>

`)

        }));


        /* =============================
        GRID
        ============================= */

        new gridjs.Grid({

            columns: [{
                    id: 'name',
                    name: 'Holiday Name'
                },
                {
                    id: 'date',
                    name: 'Date'
                },
                {
                    id: 'type',
                    name: 'Type'
                },
                {
                    id: 'aksi',
                    name: 'Action',
                    sort: false
                }
            ],

            data: data,

            search: {
                enabled: true,
                placeholder: 'Cari hari libur...'
            },

            pagination: {
                enabled: true,
                limit: 10
            },

            className: {
                table: 'table mb-0'
            }

        }).render(document.getElementById("holiday-grid"));


        /* =============================
        ADD HOLIDAY
        ============================= */

        window.addHoliday = function() {

            document.getElementById('modalTitle').innerText = "Create Holiday";

            document.getElementById('holidayForm').action =
                "{{ route('holidays.store') }}";

            document.getElementById('methodField').innerHTML = "";

            resetForm();

            modalHoliday.show();

        };


        /* =============================
        EDIT HOLIDAY
        ============================= */
        window.editHoliday = function(id) {

            const holiday = holidays.find(h => h.id == id);

            if (!holiday) {
                console.error("Holiday tidak ditemukan");
                return;
            }

            document.getElementById('modalTitle').innerText = "Edit Holiday";

            document.getElementById('holidayForm').action =
                "/holidays/" + holiday.id;

            document.getElementById('methodField').innerHTML =
                '<input type="hidden" name="_method" value="PUT">';

            resetForm();

            document.getElementById('name').value = holiday.name;

            let date = holiday.holiday_date;

            if (date) {
                date = date.split('T')[0];
            }

            document.getElementById('holiday_date').value = date;

            document.getElementById('type').value = holiday.type;

            document.getElementById('description').value =
                holiday.description ?? '';

            modalHoliday.show();

        };

        /* =============================
        DELETE HOLIDAY
        ============================= */

        window.deleteHoliday = function(id) {

            Swal.fire({

                title: "Hapus Holiday?",
                text: "Data holiday akan dihapus",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal"

            }).then((result) => {

                if (result.isConfirmed) {

                    let form = document.createElement('form');

                    form.method = "POST";
                    form.action = "/holidays/" + id;

                    form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
                `;

                    document.body.appendChild(form);
                    form.submit();

                }

            });

        };


        /* =============================
        RESET FORM
        ============================= */

        function resetForm() {

            document.querySelectorAll(
                '#holidayForm input[type=text],' +
                '#holidayForm input[type=date],' +
                '#holidayForm textarea'
            ).forEach(input => {

                input.value = '';

            });

        }

    });
</script>

@endsection
