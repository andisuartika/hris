@extends('layouts.vertical', ['title' => 'Lokasi Kantor', 'subTitle' => 'Data Master'])

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #allLocationsMap {
        height: 500px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
    }

    .leaflet-popup-content b {
        color: #3073F1;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Lokasi Kantor</h4>
                <a href="{{ route('office-locations.create') }}" class="btn btn-success">
                    <i class="ri-add-line"></i> Tambah Lokasi
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>Nama Lokasi</th>
                                <th>Instansi</th>
                                <th>Koordinat</th>
                                <th>Radius</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $loc)
                            <tr>
                                <td><strong>{{ $loc->name }}</strong></td>
                                <td>{{ $loc->company->name ?? '-' }}</td>
                                <td><small>{{ $loc->latitude }}, {{ $loc->longitude }}</small></td>
                                <td><span class="badge bg-soft-info text-info">{{ $loc->radius_meters }} Meter</span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('office-locations.edit', $loc->id) }}" class="btn btn-soft-primary btn-sm">
                                            <iconify-icon icon="solar:pen-2-broken" class="fs-18"></iconify-icon>
                                        </a>
                                        <form action="{{ route('office-locations.destroy', $loc->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm swal-confirm">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="fs-18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada data lokasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Visualisasi Geofencing</h4>
                <p class="text-muted small mb-0">Peta sebaran seluruh lokasi kantor dan radius presensi</p>
            </div>
            <div class="card-body">
                <div id="allLocationsMap"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const locations = @json($locations);

        // Inisialisasi Map (Pusat default Indonesia)
        const map = L.map('allLocationsMap').setView([-2.5489, 118.0149], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const bounds = [];

        locations.forEach(loc => {
            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);
            const rad = parseFloat(loc.radius_meters);

            if (!isNaN(lat) && !isNaN(lng)) {
                // Tambahkan Marker
                const marker = L.marker([lat, lng]).addTo(map);

                // Tambahkan Circle (Geofence)
                const circle = L.circle([lat, lng], {
                    radius: rad,
                    color: '#3073F1',
                    fillColor: '#3073F1',
                    fillOpacity: 0.1
                }).addTo(map);

                // Tambahkan Popup
                marker.bindPopup(`
                    <b>${loc.name}</b><br>
                    <small>${loc.company ? loc.company.name : '-'}</small><br>
                    <span class="badge bg-primary text-white mt-1">${rad} m</span>
                `);

                bounds.push([lat, lng]);
            }
        });

        // Fit map ke semua marker yang ada
        if (bounds.length > 0) {
            map.fitBounds(bounds, {
                padding: [50, 50]
            });
        }
    });
</script>
@endsection