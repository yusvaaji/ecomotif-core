# Ringkasan Dokumentasi Roles dan Integrasi - Bahasa Indonesia

## Overview
Dokumen ini adalah ringkasan dari dokumentasi lengkap tentang roles, fungsi-fungsi per role, dan integrasi mobile app dengan web app.

---

## Roles di Sistem

### 1. **Regular User (Buyer/Customer)**
- **Identifikasi:** `is_dealer = 0` atau `null`
- **Fungsi Utama:**
  - Browse dan search mobil
  - Add to wishlist
  - View dealer profiles
  - Kirim pesan ke dealer
  - Buat review
  - Lihat transactions (subscription history)

### 2. **Dealer/Seller**
- **Identifikasi:** `is_dealer = 1` dan `email_verified_at != null`
- **Fungsi Tambahan:**
  - Semua fungsi Regular User
  - Create car listings
  - Manage bookings (accept/cancel/complete)
  - Request withdraw
  - Manage subscription plans
  - Dashboard dengan statistik

### 3. **Admin**
- **Identifikasi:** Login via `auth:admin` middleware
- **Fungsi:**
  - Full access admin panel
  - Manage users (CRUD)
  - Approve/reject car listings
  - Manage KYC submissions
  - Manage settings

### 4. **Influencer**
- **Identifikasi:** `is_influencer = 1`
- **Status:** Field ada di database tapi belum ada implementasi fungsi khusus

---

## Struktur Database Users

### Field Penting:
- `is_dealer` (0/1) - Menentukan apakah user adalah dealer
- `is_influencer` (boolean) - Status influencer
- `kyc_status` ('enable'/'disable') - Status verifikasi KYC
- `status` ('enable'/'disable') - Status aktif akun
- `is_banned` ('yes'/'no') - Status banned
- `designation` (string) - Jabatan user
- `email_verified_at` (timestamp) - Waktu verifikasi email

---

## Integrasi Mobile App

### Authentication
- **Sistem:** JWT Authentication menggunakan `tymon/jwt-auth`
- **Login Endpoint:** `POST /api/store-login`
- **Protected Routes:** Semua route `/api/user/*` memerlukan header `Authorization: Bearer {token}`

### API Endpoints

#### Public (Tidak Perlu Auth):
- Home, Listings, Dealers, Terms, Privacy Policy
- Register, Login, Forget Password

#### Protected (Perlu Auth):
- Dashboard, Profile, Wishlist, Reviews
- Transactions, Payment

#### Dealer-Specific (Perlu `is_dealer = 1`):
- Booking Management
- Withdraw

---

## Vendor Functions yang Digunakan

### ⚠️ CATATAN: QuomodoSoft
- **Status:** Tidak ditemukan library QuomodoSoft di folder `vendor/`
- Hanya muncul sebagai **copyright text** di seeder dan setting
- Kemungkinan adalah nama vendor/company dari template, bukan library khusus
- **Perlu konfirmasi:** Di mana user melihat library QuomodoSoft?

---

### 1. **JWT Auth** (`tymon/jwt-auth`)
- `Auth::guard('api')->attempt()` - Login
- `Auth::guard('api')->user()` - Get user
- `Auth::guard('api')->logout()` - Logout

### 2. **Payment Gateways:**
- **Stripe** - `Stripe\Stripe`
- **PayPal** - PayPal SDK
- **Razorpay** - `Razorpay\Api\Api`
- **Flutterwave** - Flutterwave SDK
- **Paystack** - Paystack SDK
- **Mollie** - `Mollie\Laravel\Facades\Mollie`
- **Instamojo** - Instamojo SDK

### 3. **Social Login** (`laravel/socialite`)
- Google & Facebook login (untuk web)

### 4. **Modules System** (`nwidart/laravel-modules`)
- Car, Brand, City, Country, Currency, Language
- Subscription, KYC, GeneralSetting, Page
- Slider, BannerSlider, Feature, ContactMessage
- Newsletter, Testimonial, Blog, CompareList, Menu

---

## Contoh User

### Regular User:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "is_dealer": 0,
  "status": "enable",
  "is_banned": "no",
  "kyc_status": "disable"
}
```

### Dealer:
```json
{
  "id": 2,
  "name": "ABC Car Dealer",
  "email": "dealer@abc.com",
  "is_dealer": 1,
  "status": "enable",
  "is_banned": "no",
  "kyc_status": "enable",
  "designation": "Car Dealer"
}
```

---

## Catatan Penting untuk Modifikasi

### ⚠️ Issues yang Ditemukan:

1. **Missing Method:** 
   - `seller_store_register` direferensikan di routes tapi tidak ada implementasinya
   - Location: `routes/api.php:56`

2. **Booking Model:**
   - Model `Booking` digunakan tapi file tidak ditemukan
   - Perlu dicek atau dibuat

3. **Influencer:**
   - Field `is_influencer` ada tapi belum ada fungsi khusus

### ✅ Rekomendasi:

1. Implementasikan atau hapus `seller_store_register` dari routes
2. Buat/verifikasi Booking model
3. Dokumentasikan KYC flow lengkap
4. Review payment flow untuk semua gateway
5. Buat API documentation lengkap (Postman/Swagger)

---

## File Dokumentasi Lengkap

Untuk dokumentasi lengkap dengan detail semua endpoint, function, dan contoh, lihat:
**`DOKUMENTASI_ROLES_DAN_INTEGRASI.md`**

---

**Dibuat:** 2025-01-XX
**Berdasarkan:** Source code di `C:\Grafik\UMAR\ecomotif\mainv2\main_files`

