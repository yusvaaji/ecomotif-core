# Akses Menu Registrasi - User, Dealer, dan Mediator

## Status Saat Ini

### ✅ Yang Sudah Ada

1. **Registrasi User Biasa (Web Frontend)**
   - **URL:** `/register` atau `/auth/register`
   - **File:** `resources/views/auth/register.blade.php`
   - **Route:** `Route::get('/register', ...)` di `routes/auth.php`
   - **Status:** ✅ Sudah ada form di frontend

2. **Join as Dealer (Informasi Page)**
   - **URL:** `/join-as-dealer`
   - **Route:** `Route::get('/join-as-dealer', 'join_as_dealer')` di `routes/web.php`
   - **Status:** ✅ Ada halaman, tapi hanya informasi (belum ada form registrasi)

### ❌ Yang Belum Ada (Hanya API)

1. **Registrasi Dealer/Showroom**
   - **API Endpoint:** `POST /api/seller/store-register`
   - **Status:** ❌ Belum ada form di frontend, hanya API

2. **Registrasi Mediator**
   - **API Endpoint:** `POST /api/mediator/store-register`
   - **Status:** ❌ Belum ada form di frontend, hanya API

---

## Opsi Implementasi

### Opsi 1: Menggunakan API Saja (Mobile App)

Jika aplikasi mobile sudah menggunakan API, maka:
- ✅ Tidak perlu membuat halaman web
- ✅ Mobile app langsung call API endpoint
- ✅ Dokumentasi API sudah lengkap di `DOKUMENTASI_REGISTRASI.md`

**Cara Akses:**
- Mobile app → Form Registrasi → Call API endpoint

---

### Opsi 2: Membuat Halaman Web Frontend

Jika perlu halaman web untuk registrasi dealer/mediator:

#### A. Halaman Registrasi Dealer

**File yang perlu dibuat:**
- `resources/views/auth/register-dealer.blade.php`
- Route di `routes/web.php`

**URL yang akan dibuat:**
- `/register-dealer` atau `/join-as-dealer/register`

**Form Fields:**
- Name
- Email
- Password
- Password Confirmation
- Phone
- Address

**Action:**
- Submit ke API: `POST /api/seller/store-register`

---

#### B. Halaman Registrasi Mediator

**File yang perlu dibuat:**
- `resources/views/auth/register-mediator.blade.php`
- Route di `routes/web.php`

**URL yang akan dibuat:**
- `/register-mediator`

**Form Fields:**
- Name
- Email
- Password
- Password Confirmation
- Phone
- Address
- Showroom (dropdown, optional)

**Action:**
- Submit ke API: `POST /api/mediator/store-register`

---

## Rekomendasi Implementasi

### Untuk Web Frontend:

1. **Modifikasi Halaman `/join-as-dealer`**
   - Tambahkan form registrasi dealer di halaman yang sudah ada
   - Atau buat link ke halaman registrasi baru

2. **Buat Halaman Baru untuk Mediator**
   - Buat halaman `/register-mediator`
   - Tambahkan link di footer atau menu

3. **Tambah Link di Menu/Footer**
   - Link "Daftar sebagai Dealer" → `/register-dealer`
   - Link "Daftar sebagai Mediator" → `/register-mediator`

---

## Struktur Menu yang Disarankan

### Di Header/Navbar:
```
Home | Listings | Dealers | About | Contact
```

### Di Footer:
```
Quick Links:
- Register (User)
- Join as Dealer
- Join as Mediator
- Login
```

### Atau di Dropdown Menu:
```
Account
├── Login
├── Register (User)
├── Join as Dealer
└── Join as Mediator
```

---

## Contoh Implementasi Halaman Registrasi

### 1. Route untuk Registrasi Dealer (Web)

Tambahkan di `routes/web.php`:

```php
Route::get('/register-dealer', function () {
    return view('auth.register-dealer');
})->name('register-dealer');
```

### 2. View untuk Registrasi Dealer

Buat file `resources/views/auth/register-dealer.blade.php`:

```blade
@extends('layout')
@section('title')
    <title>{{ __('translate.Join as Dealer') }}</title>
@endsection

@section('body-content')
<main>
    <section class="login">
        <div class="container">
            <div class="row login-bg">
                <div class="col-lg-6">
                    <div class="login-head">
                        <h3>{{ __('translate.Join as Dealer') }}</h3>
                        <span>{{ __('translate.Register as Dealer/Showroom') }}</span>
                    </div>

                    <form id="register-dealer-form">
                        @csrf
                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Name') }} <span>*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Email') }} <span>*</span></label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Phone') }} <span>*</span></label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Address') }} <span>*</span></label>
                                <textarea class="form-control" name="address" required></textarea>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Password') }} <span>*</span></label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <div class="login-form-inner">
                                <label class="form-label">{{ __('translate.Confirm Password') }} <span>*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <button type="submit" class="thm-btn-two">{{ __('translate.Register') }}</button>
                        </div>
                    </form>

                    <div class="create-accoun-text">
                        <p>{{ __('translate.Already have an account ?') }}
                            <span><a href="{{ route('login') }}">{{ __('translate.Sign In Here') }}</a></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('js_section')
<script>
$(document).ready(function() {
    $('#register-dealer-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/api/seller/store-register',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response.message);
                // Redirect to verification page
                window.location.href = '/user-verification?email=' + $('input[name="email"]').val();
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = '';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value[0] + '\n';
                    });
                    alert(errors);
                } else {
                    alert('Registration failed. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush
```

---

## Link Menu yang Perlu Ditambahkan

### Di Layout/Header

Tambahkan di `resources/views/layout.blade.php` atau file header:

```blade
<!-- Di menu navigation -->
<li><a href="{{ route('register') }}">Register</a></li>
<li><a href="{{ route('register-dealer') }}">Join as Dealer</a></li>
<li><a href="{{ route('register-mediator') }}">Join as Mediator</a></li>
```

### Di Footer

Tambahkan di `resources/views/footer.blade.php`:

```blade
<div class="footer-links">
    <h4>Quick Links</h4>
    <ul>
        <li><a href="{{ route('register') }}">Register</a></li>
        <li><a href="{{ route('register-dealer') }}">Join as Dealer</a></li>
        <li><a href="{{ route('register-mediator') }}">Join as Mediator</a></li>
        <li><a href="{{ route('login') }}">Login</a></li>
    </ul>
</div>
```

---

## Summary

### Saat Ini:
- ✅ Registrasi User: Ada form di `/register`
- ✅ Join as Dealer: Ada halaman info di `/join-as-dealer` (tapi belum ada form)
- ❌ Registrasi Dealer: Hanya API (`POST /api/seller/store-register`)
- ❌ Registrasi Mediator: Hanya API (`POST /api/mediator/store-register`)

### Yang Perlu Dibuat (Jika Butuh Web Frontend):
1. Form registrasi dealer di halaman `/join-as-dealer` atau halaman baru
2. Form registrasi mediator di halaman baru `/register-mediator`
3. Link menu di header/footer untuk akses registrasi

### Jika Hanya Mobile App:
- ✅ Tidak perlu membuat halaman web
- ✅ Gunakan API endpoint langsung
- ✅ Dokumentasi sudah lengkap

---

**Pilih opsi yang sesuai dengan kebutuhan:**
- **Opsi 1:** Gunakan API saja (untuk mobile app)
- **Opsi 2:** Buat halaman web frontend (untuk web browser)




