@extends('layouts.auth', ['title' => 'Masuk'])

@section('content')
<div class="col-xl-5">

    <div class="card auth-card">
        <div class="card-body px-3 py-5">
            <div class="mx-auto mb-4 text-center auth-logo">
                <a href="/" class="logo-dark">
                    <img src="/images/logo-hris.png" height="50" alt="logo dark">
                </a>

                <a href="/" class="logo-light">
                    <img src="/images/logo-hris.png" height="50" alt="logo light">
                </a>
            </div>

            <h2 class="fw-bold text-uppercase text-center fs-18">Masuk</h2>
            <p class="text-muted text-center mt-1 mb-4">Masukkan alamat email dan kata sandi Anda untuk mengakses panel admin.</p>

            <div class="px-4">
                <form method="POST" action="{{ route('login') }}" class="authentication-form">
                    @csrf

                    {{-- Notifikasi Error Validasi --}}
                    @if (sizeof($errors) > 0)
                    @foreach ($errors->all() as $error)
                    <p class="text-danger mb-3">{{ $error }}</p>
                    @endforeach
                    @endif

                    {{-- Notifikasi Sukses Logout --}}
                    @if (session('success'))
                    <p class="text-success mb-3">{{ session('success') }}</p>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="example-email">Email</label>
                        <input type="email" id="example-email" name="email"
                            class="form-control bg-light bg-opacity-50 border-light py-2"
                            placeholder="Masukkan email Anda" value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="example-password">Kata Sandi</label>
                        <input type="password" id="example-password" name="password"
                            class="form-control bg-light bg-opacity-50 border-light py-2"
                            placeholder="Masukkan kata sandi Anda">
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            {{-- Tambahkan name="remember" agar bisa ditangkap request --}}
                            <input type="checkbox" class="form-check-input" id="checkbox-signin" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="checkbox-signin">Ingat saya</label>
                        </div>
                    </div>

                    <div class="mb-1 text-center d-grid">
                        <button class="btn btn-primary py-2 fw-medium" type="submit">Masuk</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
