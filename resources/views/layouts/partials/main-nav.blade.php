<div class="main-nav">
    <div class="logo-box">
        <a href="#" class="logo-dark">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
        </a>

        <a href="#" class="logo-light">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-light.png" class="logo-lg" alt="logo light">
        </a>
    </div>

    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
    </button>

    <div class="scrollbar" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menu Utama</li>

            {{-- DASHBOARD --}}
            @canany(['dashboard:admin', 'dashboard:pegawai'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                    <span class="nav-icon"><i class="ri-dashboard-2-line"></i></span>
                    <span class="nav-text"> Dashboards </span>
                </a>
                <div class="collapse" id="sidebarDashboards">
                    <ul class="nav sub-navbar-nav">
                        @can('dashboard:admin')
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="#">Ringkasan Admin</a>
                        </li>
                        @endcan
                        @can('dashboard:pegawai')
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="#">Dasbor Pegawai</a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- DATA MASTER --}}
            @canany(['master:perusahaan', 'master:lokasi', 'master:departemen', 'master:jabatan'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarMasterData" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMasterData">
                    <span class="nav-icon"><i class="ri-building-line"></i></span>
                    <span class="nav-text"> Data Master </span>
                </a>
                <div class="collapse" id="sidebarMasterData">
                    <ul class="nav sub-navbar-nav">
                        @can('master:perusahaan')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('companies.index') }}">Instansi / Perusahaan</a></li>
                        @endcan
                        @can('master:lokasi')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('office-locations.index') }}">Lokasi Kantor (GPS)</a></li>
                        @endcan
                        @can('master:departemen')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('departments.index') }}">Departemen / Divisi</a></li>
                        @endcan
                        @can('master:jabatan')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('positions.index') }}">Posisi & Jabatan</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- KEPEGAWAIAN --}}
            @canany(['pegawai:index', 'pegawai:wajah', 'pegawai:resign'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarEmployees" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEmployees">
                    <span class="nav-icon"><i class="ri-team-line"></i></span>
                    <span class="nav-text"> Kepegawaian </span>
                </a>
                <div class="collapse" id="sidebarEmployees">
                    <ul class="nav sub-navbar-nav">
                        @can('pegawai:index')
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('employees.index') }}">Data Pegawai</a>
                        </li>
                        @endcan
                        @can('pegawai:wajah')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Pendaftaran Wajah</a></li>
                        @endcan
                        @can('pegawai:resign')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Catatan Resign/Keluar</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- ABSENSI --}}
            @canany(['absensi:log', 'absensi:lembur', 'absensi:shift', 'absensi:hari-libur'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarAttendance" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAttendance">
                    <span class="nav-icon"><i class="ri-calendar-check-line"></i></span>
                    <span class="nav-text"> Absensi & Kehadiran </span>
                </a>
                <div class="collapse" id="sidebarAttendance">
                    <ul class="nav sub-navbar-nav">
                        @can('absensi:log')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Log Kehadiran Harian</a></li>
                        @endcan
                        @can('absensi:lembur')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Pengajuan Lembur</a></li>
                        @endcan
                        @can('absensi:shift')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Jadwal Kerja (Shift)</a></li>
                        @endcan
                        @can('absensi:hari-libur')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Hari Libur Nasional</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- CUTI & IZIN --}}
            @canany(['cuti:pengajuan', 'cuti:approval', 'cuti:saldo', 'cuti:tipe'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarLeave" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarLeave">
                    <span class="nav-icon"><i class="ri-flight-takeoff-line"></i></span>
                    <span class="nav-text"> Cuti & Izin </span>
                </a>
                <div class="collapse" id="sidebarLeave">
                    <ul class="nav sub-navbar-nav">
                        @can('cuti:pengajuan')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Pengajuan Cuti</a></li>
                        @endcan
                        @can('cuti:approval')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Persetujuan (Approval)</a></li>
                        @endcan
                        @can('cuti:saldo')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Saldo Cuti Pegawai</a></li>
                        @endcan
                        @can('cuti:tipe')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Pengaturan Tipe Cuti</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- PAYROLL --}}
            @canany(['payroll:proses', 'payroll:komponen', 'payroll:slip'])
            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarPayroll" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPayroll">
                    <span class="nav-icon"><i class="ri-wallet-3-line"></i></span>
                    <span class="nav-text"> Penggajian (Payroll) </span>
                </a>
                <div class="collapse" id="sidebarPayroll">
                    <ul class="nav sub-navbar-nav">
                        @can('payroll:proses')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Proses Gaji Bulanan</a></li>
                        @endcan
                        @can('payroll:komponen')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Komponen Gaji</a></li>
                        @endcan
                        @can('payroll:slip')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Slip Gaji Pegawai</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

            {{-- PENGUMUMAN --}}
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span class="nav-icon"><i class="ri-megaphone-line"></i></span>
                    <span class="nav-text">Pengumuman</span>
                    <span class="badge bg-danger badge-pill text-end">Baru</span>
                </a>
            </li>

            {{-- KONFIGURASI SISTEM --}}
            @canany(['setting:absensi', 'setting:user', 'setting:role-permission'])
            <li class="menu-title">Konfigurasi Sistem</li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSettings">
                    <span class="nav-icon"><i class="ri-settings-3-line"></i></span>
                    <span class="nav-text"> Pengaturan </span>
                </a>
                <div class="collapse" id="sidebarSettings">
                    <ul class="nav sub-navbar-nav">
                        @can('setting:absensi')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Setting Absensi (Face/GPS)</a></li>
                        @endcan
                        @can('setting:user')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Pengguna (Users)</a></li>
                        @endcan
                        @can('setting:role-permission')
                        <li class="sub-nav-item"><a class="sub-nav-link" href="#">Peran & Hak Akses</a></li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcanany

        </ul>
    </div>
</div>
