# Panduan Integrasi SSO Keycloak

Dokumen ini menjelaskan cara mengintegrasikan aplikasi lain ke SSO Keycloak yang sudah berjalan di project `laravel-sso`.

---

## Prasyarat

- Keycloak berjalan dan dapat diakses (default: `http://localhost:8080`, production: domain publik)
- Akses ke admin console Keycloak
- Realm `myrealm` sudah dikonfigurasi

---

## Langkah 1 — Daftarkan Client Baru di Keycloak

1. Buka admin console: `http://localhost:8080`
2. Login dengan `admin` / `admin`
3. Pilih realm **myrealm** (pojok kiri atas)
4. Klik menu **Clients** → **Create client**

### Isi form:

| Field | Value |
|-------|-------|
| Client type | `OpenID Connect` |
| Client ID | nama unik app Anda, misal: `app-kepegawaian` |

Klik **Next**.

| Field | Value |
|-------|-------|
| Client authentication | `ON` |
| Authorization | `OFF` |
| Standard flow | `ON` |

Klik **Next**.

| Field | Value |
|-------|-------|
| Valid redirect URIs | URL callback app Anda, misal: `http://localhost:3001/auth/callback` |
| Web origins | URL app Anda, misal: `http://localhost:3001` |

Klik **Save**.

5. Buka tab **Credentials** → copy **Client Secret**

---

## Langkah 2 — Integrasi per Framework

---

### Laravel

#### Install package

```bash
composer require laravel/socialite socialiteproviders/keycloak
```

#### config/services.php

```php
'keycloak' => [
    'client_id'     => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
    'redirect'      => env('KEYCLOAK_REDIRECT_URI'),
    'base_url'      => env('KEYCLOAK_BASE_URL'),
    'realms'        => env('KEYCLOAK_REALM'),
],
```

#### .env

```env
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=myrealm
KEYCLOAK_CLIENT_ID=app-kepegawaian
KEYCLOAK_CLIENT_SECRET=<client-secret>
KEYCLOAK_REDIRECT_URI=http://localhost:3001/auth/callback
```

#### bootstrap/app.php atau EventServiceProvider

```php
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Keycloak\KeycloakExtendSocialite;

// Di withEvents atau boot():
Event::listen(SocialiteWasCalled::class, KeycloakExtendSocialite::class);
```

#### AuthController.php

```php
use Laravel\Socialite\Facades\Socialite;

// Redirect ke Keycloak
public function redirect()
{
    return Socialite::driver('keycloak')->redirect();
}

// Handle callback
public function callback()
{
    $socialUser = Socialite::driver('keycloak')->user();

    $raw   = $socialUser->getRaw();
    $roles = collect($raw['roles'] ?? [])
        ->filter(fn($r) => in_array($r, ['admin', 'manager', 'pegawai']))
        ->values()->all();

    $user = User::updateOrCreate(
        ['keycloak_id' => $socialUser->getId()],
        [
            'name'  => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'roles' => $roles,
        ]
    );

    Auth::login($user);
    return redirect('/dashboard');
}
```

#### routes/web.php

```php
Route::get('/auth/keycloak', [AuthController::class, 'redirect'])->name('keycloak.redirect');
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('keycloak.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
```

#### Logout (kembali ke Keycloak)

```php
public function logout()
{
    Auth::logout();
    $logoutUrl = env('KEYCLOAK_BASE_URL')
        . '/realms/' . env('KEYCLOAK_REALM')
        . '/protocol/openid-connect/logout?redirect_uri='
        . urlencode(url('/'));
    return redirect($logoutUrl);
}
```

---

### Next.js (menggunakan NextAuth.js)

#### Install

```bash
npm install next-auth
```

#### .env.local

```env
NEXTAUTH_URL=http://localhost:3001
NEXTAUTH_SECRET=random-secret-string

KEYCLOAK_CLIENT_ID=app-kepegawaian
KEYCLOAK_CLIENT_SECRET=<client-secret>
KEYCLOAK_ISSUER=http://localhost:8080/realms/myrealm
```

#### app/api/auth/[...nextauth]/route.ts

```typescript
import NextAuth from 'next-auth';
import KeycloakProvider from 'next-auth/providers/keycloak';

const handler = NextAuth({
  providers: [
    KeycloakProvider({
      clientId:     process.env.KEYCLOAK_CLIENT_ID!,
      clientSecret: process.env.KEYCLOAK_CLIENT_SECRET!,
      issuer:       process.env.KEYCLOAK_ISSUER,
    }),
  ],
  callbacks: {
    async jwt({ token, account, profile }) {
      if (account) {
        token.accessToken = account.access_token;
        // Ambil roles dari token Keycloak
        const roles = (profile as any)?.roles ?? [];
        token.roles = roles.filter((r: string) =>
          ['admin', 'manager', 'pegawai'].includes(r)
        );
      }
      return token;
    },
    async session({ session, token }) {
      (session as any).roles = token.roles;
      (session as any).accessToken = token.accessToken;
      return session;
    },
  },
});

export { handler as GET, handler as POST };
```

#### Penggunaan di komponen

```typescript
import { useSession, signIn, signOut } from 'next-auth/react';

export default function Page() {
  const { data: session } = useSession();

  if (!session) return <button onClick={() => signIn('keycloak')}>Login</button>;

  return (
    <div>
      <p>Halo, {session.user?.name}</p>
      <button onClick={() => signOut()}>Logout</button>
    </div>
  );
}
```

---

### Vue.js / Nuxt (menggunakan nuxt-auth-utils atau oauth manual)

#### .env

```env
NUXT_OAUTH_KEYCLOAK_CLIENT_ID=app-kepegawaian
NUXT_OAUTH_KEYCLOAK_CLIENT_SECRET=<client-secret>
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=myrealm
```

#### Flow manual (universal)

```
1. Redirect user ke:
   GET http://localhost:8080/realms/myrealm/protocol/openid-connect/auth
     ?client_id=app-kepegawaian
     &redirect_uri=http://localhost:3001/auth/callback
     &response_type=code
     &scope=openid profile email

2. Keycloak redirect balik ke callback dengan ?code=xxx

3. Tukar code dengan token:
   POST http://localhost:8080/realms/myrealm/protocol/openid-connect/token
   Body (form-urlencoded):
     grant_type=authorization_code
     client_id=app-kepegawaian
     client_secret=<secret>
     code=<code dari step 2>
     redirect_uri=http://localhost:3001/auth/callback

4. Response berisi access_token, id_token, refresh_token

5. Decode id_token (JWT) untuk mendapatkan data user & roles
```

---

## Langkah 3 — Mendapatkan Data User dari Token

Setelah login, token Keycloak mengandung klaim berikut (jika protocol mapper sudah dikonfigurasi):

```json
{
  "sub": "uuid-keycloak-user-id",
  "name": "Andi Suartika",
  "email": "andi@instansi.go.id",
  "preferred_username": "andi",
  "roles": ["pegawai"],
  "nip": "198501012010011001",
  "jabatan": "Staff IT",
  "departemen": "Teknologi Informasi",
  "unit_kerja": "Divisi Pengembangan Sistem",
  "no_hp": "081234567890",
  "status_pegawai": "aktif"
}
```

> **Catatan:** Klaim kustom (nip, jabatan, dll) hanya tersedia jika Protocol Mapper sudah ditambahkan di Keycloak untuk client tersebut. Lihat bagian **Protocol Mapper** di bawah.

---

## Langkah 4 — Menambahkan Protocol Mapper (Klaim Kustom)

Agar token mengandung data kepegawaian (NIP, jabatan, dll):

1. Admin console → **myrealm** → **Clients** → pilih client Anda
2. Tab **Client scopes** → klik scope `<client-id>-dedicated`
3. **Add mapper** → **By configuration** → **User Attribute**
4. Isi untuk setiap atribut:

| Field | Value (contoh NIP) |
|-------|-------------------|
| Name | `nip` |
| User Attribute | `nip` |
| Token Claim Name | `nip` |
| Claim JSON Type | `String` |
| Add to ID token | `ON` |
| Add to access token | `ON` |

Ulangi untuk: `jabatan`, `departemen`, `unit_kerja`, `no_hp`, `status_pegawai`

---

## Langkah 5 — Single Logout

Agar logout dari satu aplikasi juga logout dari semua aplikasi SSO:

```
GET http://localhost:8080/realms/myrealm/protocol/openid-connect/logout
  ?redirect_uri=http://localhost:3001/
```

Keycloak akan menginvalidasi semua sesi aktif user tersebut di semua client.

---

## Ringkasan URL Penting Keycloak

| Endpoint | URL |
|----------|-----|
| Admin Console | `http://localhost:8080` |
| Authorization | `http://localhost:8080/realms/myrealm/protocol/openid-connect/auth` |
| Token | `http://localhost:8080/realms/myrealm/protocol/openid-connect/token` |
| Userinfo | `http://localhost:8080/realms/myrealm/protocol/openid-connect/userinfo` |
| Logout | `http://localhost:8080/realms/myrealm/protocol/openid-connect/logout` |
| JWKS (public key) | `http://localhost:8080/realms/myrealm/protocol/openid-connect/certs` |
| Discovery | `http://localhost:8080/realms/myrealm/.well-known/openid-configuration` |

> Untuk **production**, ganti `http://localhost:8080` dengan domain publik Keycloak, misal `https://sso.instansi.go.id`.

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `Invalid redirect_uri` | Pastikan URI di client Keycloak sama persis dengan yang dipakai app |
| `HTTPS required` | Tambahkan `KC_HTTP_ENABLED=true` dan `KC_HOSTNAME_STRICT=false` di env Keycloak |
| Token tidak berisi roles | Pastikan user sudah di-assign role di Keycloak dan mapper roles sudah aktif |
| `Invalid token issuer` | Pastikan `KEYCLOAK_BASE_URL` di app sama dengan hostname yang diakses browser |
| CORS error | Tambahkan URL app di **Web origins** pada settings client Keycloak |
