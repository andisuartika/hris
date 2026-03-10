@extends('layouts.vertical', [
    'title' => isset($location) ? 'Edit Lokasi' : 'Tambah Lokasi',
    'titleRoute' => route('office-locations.index'),
    'subTitle' => 'Lokasi Kantor',
    'subTitleRoute' => route('office-locations.index'),
    'parentTitle' => 'Master Data',
    'parentRoute' => '#'
])
@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 450px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #ddd;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">{{ isset($location) ? 'Form Edit' : 'Form Tambah' }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ isset($location) ? route('office-locations.update', $location->id) : route('office-locations.store') }}" method="POST">
                    @csrf
                    @isset($location) @method('PUT') @endisset

                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $location->name ?? '') }}" required placeholder="Contoh: Kantor Cabang Denpasar">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instansi <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select" required>
                            <option value="">Pilih Instansi...</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $location->company_id ?? '') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Radius Presensi (Meter) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="radius_meters" id="radius_input" class="form-control" value="{{ old('radius_meters', $location->radius_meters ?? 50) }}" min="10" required>
                            <span class="input-group-text">m</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" id="lat" class="form-control bg-light" value="{{ old('latitude', $location->latitude ?? '') }}" readonly required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="lng" class="form-control bg-light" value="{{ old('longitude', $location->longitude ?? '') }}" readonly required>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary w-100">Simpan Data</button>
                        <a href="{{ route('office-locations.index') }}" class="btn btn-light w-100">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h4 class="card-title">Pilih Titik Lokasi</h4>
                <span class="badge bg-soft-primary text-primary">Klik pada peta untuk menentukan posisi</span>
            </div>
            <div class="card-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map, marker = null, circle = null;


    const initialLat = parseFloat(@json($location->latitude ?? -8.6500));
    const initialLng = parseFloat(@json($location->longitude ?? 115.2167));
    const initialRadius = parseFloat(@json($location->radius_meters ?? 50));
    const isEdit = @json(isset($location));

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Inisialisasi Map
        map = L.map('map').setView([initialLat, initialLng], isEdit ? 17 : 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 2. Load Marker Awal jika dalam mode Edit
        if (isEdit) {
            setMarker(initialLat, initialLng, initialRadius);
        }

        // 3. Listener Klik Map
        map.on('click', function(e) {
            setMarker(e.latlng.lat, e.latlng.lng);
        });

        // 4. Listener Input Radius (Live Update Circle)
        const radiusInput = document.getElementById('radius_input');
        if (radiusInput) {
            radiusInput.addEventListener('input', function() {
                const newRad = parseFloat(this.value) || 0;
                if (circle) circle.setRadius(newRad);
            });
        }
    });

    /**
     * Fungsi untuk mengatur Marker dan Circle pada peta
     */
    function setMarker(lat, lng, radius = null) {
        const inputElement = document.getElementById('radius_input');
        const rad = (radius !== null) ? radius : (parseFloat(inputElement.value) || 50);

        // Bersihkan layer lama jika ada
        if (marker !== null) map.removeLayer(marker);
        if (circle !== null) map.removeLayer(circle);

        const nLat = parseFloat(lat);
        const nLng = parseFloat(lng);

        // Tambahkan Marker & Circle baru
        marker = L.marker([nLat, nLng]).addTo(map);
        circle = L.circle([nLat, nLng], {
            radius: rad,
            color: '#3073F1',
            fillColor: '#3073F1',
            fillOpacity: 0.2
        }).addTo(map);

        // Update form inputs
        document.getElementById('lat').value = nLat.toFixed(8);
        document.getElementById('lng').value = nLng.toFixed(8);

        // Sinkronisasi input radius jika fungsi dipanggil dengan parameter radius (saat init edit)
        if (radius !== null && inputElement) {
            inputElement.value = radius;
        }

        map.panTo([nLat, nLng]);
    }
</script>
@endsection
