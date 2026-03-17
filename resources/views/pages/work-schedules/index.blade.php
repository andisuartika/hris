@extends('layouts.vertical', ['title' => 'Work Schedule', 'subTitle' => 'Sistem'])

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
                    <h4 class="card-title">Work Schedule</h4>
                    <p class="text-muted mb-0 fs-13">Kelola jadwal kerja harian karyawan.</p>
                </div>

                <button onclick="addSchedule()" class="btn btn-sm btn-primary">
                    <i class="ri-time-line me-1"></i> Tambah Jadwal
                </button>
            </div>

            <div class="card-body p-0">
                <div id="schedule-grid"></div>
            </div>

        </div>
    </div>
</div>


{{-- MODAL --}}
<div class="modal fade" id="modalSchedule" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0">
            <form id="scheduleForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">Work Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Schedule Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" id="code" class="form-control" required>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-3">Daily Schedule</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="120">Day</th>
                                    <th width="120">Working</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Break Start</th>
                                    <th>Break End</th>
                                    <th>Work Hours</th>
                                    <th>Tolerance Late</th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                $days=[
                                1=>'Monday',
                                2=>'Tuesday',
                                3=>'Wednesday',
                                4=>'Thursday',
                                5=>'Friday',
                                6=>'Saturday',
                                7=>'Sunday'
                                ];
                                @endphp

                                @foreach($days as $num=>$day)

                                <tr>
                                    <td class="fw-semibold">{{ $day }}</td>
                                    <td>
                                        <input type="hidden" name="days[{{ $num }}][is_working_day]" value="0">
                                        <input type="checkbox"
                                            name="days[{{ $num }}][is_working_day]"
                                            value="1">
                                    </td>

                                    <td>
                                        <input type="time"
                                            name="days[{{ $num }}][start_time]"
                                            class="form-control">
                                    </td>

                                    <td>
                                        <input type="time"
                                            name="days[{{ $num }}][end_time]"
                                            class="form-control">
                                    </td>

                                    <td>
                                        <input type="time"
                                            name="days[{{ $num }}][break_start]"
                                            class="form-control">
                                    </td>

                                    <td>
                                        <input type="time"
                                            name="days[{{ $num }}][break_end]"
                                            class="form-control">
                                    </td>

                                    <td>
                                        <input type="number"
                                            step="0.1"
                                            name="days[{{ $num }}][work_hours]"
                                            class="form-control">
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="days[{{ $num }}][tolerance_late]"
                                            class="form-control">
                                    </td>

                                </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Simpan
                    </button>
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


        const modal = new bootstrap.Modal(document.getElementById('modalSchedule'));

        const schedules = @json($schedules);

        const data = schedules.map(schedule => ({
            name: schedule.name,
            code: schedule.code,
            aksi: gridjs.html(`
                <button onclick='editSchedule(${schedule.id})'
                    class="btn btn-soft-primary btn-sm">
                    Edit
                </button>

                <form action="/work-schedules/${schedule.id}"
                    method="POST"
                    class="d-inline form-delete">

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">

                    <button type="button"
                        class="btn btn-soft-danger btn-sm swal-confirm"
                        data-title="Hapus Jadwal Kerja?"
                        data-text="Data ${schedule.name} akan dihapus"
                        data-confirm="Ya, Hapus">
                        Delete
                    </button>

                </form>
            `)
        }));


        new gridjs.Grid({

            columns: [{
                    id: 'name',
                    name: 'Schedule'
                },
                {
                    id: 'code',
                    name: 'Code'
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
                placeholder: 'Cari jadwal kerja...'
            },

            pagination: {
                enabled: true,
                limit: 10
            },

            className: {
                table: 'table mb-0'
            }

        }).render(document.getElementById("schedule-grid"));


        window.addSchedule = function() {

            document.getElementById('modalTitle').innerText = "Create Work Schedule";

            document.getElementById('scheduleForm').action =
                "{{ route('work-schedules.store') }}";

            document.getElementById('methodField').innerHTML = "";

            resetForm();

            modal.show();
        }


        window.editSchedule = function(id) {

            const schedule = schedules.find(s => s.id == id);

            document.getElementById('modalTitle').innerText = "Edit Work Schedule";

            document.getElementById('scheduleForm').action =
                "/work-schedules/" + schedule.id;

            document.getElementById('methodField').innerHTML =
                '<input type="hidden" name="_method" value="PUT">';

            resetForm();

            document.getElementById('name').value = schedule.name;
            document.getElementById('code').value = schedule.code;

            schedule.days.forEach(day => {

                let d = day.day_of_week;

                const checkbox =
                    document.querySelector(`[name="days[${d}][is_working_day]"][type="checkbox"]`);

                if (checkbox) {
                    checkbox.checked = !!day.is_working_day;
                }

                const start = document.querySelector(`[name="days[${d}][start_time]"]`);
                const end = document.querySelector(`[name="days[${d}][end_time]"]`);
                const breakStart = document.querySelector(`[name="days[${d}][break_start]"]`);
                const breakEnd = document.querySelector(`[name="days[${d}][break_end]"]`);
                const workHours = document.querySelector(`[name="days[${d}][work_hours]"]`);
                const tolerance = document.querySelector(`[name="days[${d}][tolerance_late]"]`);

                if (start) start.value = day.start_time ?? '';
                if (end) end.value = day.end_time ?? '';
                if (breakStart) breakStart.value = day.break_start ?? '';
                if (breakEnd) breakEnd.value = day.break_end ?? '';
                if (workHours) workHours.value = day.work_hours ?? '';
                if (tolerance) tolerance.value = day.tolerance_late ?? '';

            });

            modal.show();
        }


        function resetForm() {

            document.querySelectorAll(
                '#scheduleForm input[type=text], ' +
                '#scheduleForm input[type=time], ' +
                '#scheduleForm input[type=number]'
            ).forEach(input => {
                input.value = '';
            });

            document.querySelectorAll('#scheduleForm input[type=checkbox]')
                .forEach(cb => {
                    cb.checked = false;
                });
        }

    });
</script>

@endsection
