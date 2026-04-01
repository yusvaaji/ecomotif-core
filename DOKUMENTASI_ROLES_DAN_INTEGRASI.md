# Dokumentasi Roles dan Integrasi Mobile App

## Daftar Isi
1. [Struktur Roles di Database](#struktur-roles-di-database)
2. [Fungsi-Fungsi Per Role](#fungsi-fungsi-per-role)
3. [Contoh User Per Role](#contoh-user-per-role)
4. [Integrasi Mobile App dengan Web App](#integrasi-mobile-app-dengan-web-app)
5. [Function dari Vendor yang Digunakan](#function-dari-vendor-yang-digunakan)

---

## Struktur Roles di Database

### Tabel `users`
Sistem menggunakan tabel `users` dengan field-field berikut untuk menentukan role:

#### Field-field Penting untuk Roles:
- **`is_dealer`** (integer, default: 0)
  - `0` = Regular User (Buyer/Customer)
  - `1` = Dealer/Seller
  
- **`is_influencer`** (boolean, nullable)
  - Menandakan apakah user adalah influencer
  
- **`kyc_status`** (enum: 'enable'/'disable', default: 'disable')
  - Status verifikasi KYC (Know Your Customer)
  - Required untuk menjadi dealer
  
- **`status`** (enum: 'enable'/'disable', default: 'enable')
  - Status aktif/non-aktif akun
  
- **`is_banned`** (enum: 'yes'/'no', default: 'no')
  - Status banned akun
  
- **`designation`** (string, nullable)
  - Jabatan/posisi user (contoh: "Car Dealer", "Sales Manager")
  
- **`email_verified_at`** (timestamp, nullable)
  - Waktu verifikasi email (required untuk dealer)

### Tabel `admins`
Role Admin terpisah di tabel `admins`:
- **`name`** (string)
- **`email`** (string, unique)
- **`password`** (string, hashed)
- **`image`** (string, nullable)
- **`designation`** (string, nullable)

---

## Fungsi-Fungsi Per Role

### 1. REGULAR USER (Buyer/Customer)
**Identifikasi:** `is_dealer = 0` atau `is_dealer = null`

#### Fungsi yang Dapat Diakses:

##### A. Autentikasi & Profil
- **Register** (`POST /api/store-register`)
  - Controller: `App\Http\Controllers\API\Auth\RegisterController@store_register`
  - Membuat akun baru dengan verifikasi OTP via email
  
- **Login** (`POST /api/store-login`)
  - Controller: `App\Http\Controllers\API\Auth\LoginController@store_login`
  - Menggunakan JWT authentication
  - Return: `access_token`, `user_type: 'user'`
  
- **Verifikasi Email** (`POST /api/user-verification`)
  - Controller: `App\Http\Controllers\API\Auth\RegisterController@register_verification`
  - Verifikasi dengan OTP
  
- **Forget Password** (`POST /api/send-forget-password`)
  - Controller: `App\Http\Controllers\API\Auth\LoginController@send_custom_forget_pass`
  - Mengirim OTP reset password
  
- **Reset Password** (`POST /api/store-reset-password`)
  - Controller: `App\Http\Controllers\API\Auth\LoginController@store_reset_password`

##### B. Dashboard & Profil (Requires Auth)
- **Dashboard** (`GET /api/user/dashboard`)
  - Controller: `App\Http\Controllers\API\ProfileController@dashboard`
  - Menampilkan: user info, cars (jika ada), total car, total featured car, total wishlist
  
- **Edit Profile** (`GET /api/user/edit-profile`)
  - Controller: `App\Http\Controllers\API\ProfileController@edit`
  
- **Update Profile** (`PUT /api/user/update-profile`)
  - Controller: `App\Http\Controllers\API\ProfileController@update`
  - Update: name, phone, address, designation, image, banner_image
  
- **Change Password** (`POST /api/user/update-password`)
  - Controller: `App\Http\Controllers\API\ProfileController@update_password`

##### C. Listing & Pencarian
- **Home/Index** (`GET /api/`)
  - Controller: `App\Http\Controllers\API\HomeController@index`
  - Menampilkan: sliders, brands, featured cars, ads banners, dealers, latest cars
  
- **Listings** (`GET /api/listings`)
  - Controller: `App\Http\Controllers\API\HomeController@listings`
  - Filter: country, city, brands, condition, purpose, search
  
- **Listing Detail** (`GET /api/listing/{id}`)
  - Controller: `App\Http\Controllers\API\HomeController@listing`
  - Detail mobil dengan galleries, related listings, dealer info, reviews
  
- **All Brands** (`GET /api/all-brands`)
  - Controller: `App\Http\Controllers\API\HomeController@all_brands`

##### D. Wishlist
- **Add to Wishlist** (`GET /api/user/add-to-wishlist/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@add_to_wishlist`
  
- **Wishlists** (`GET /api/user/wishlists`)
  - Controller: `App\Http\Controllers\API\ProfileController@wishlists`
  
- **Remove Wishlist** (`DELETE /api/user/remove-wishlist/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@remove_wishlist`

##### E. Reviews
- **Reviews** (`GET /api/user/reviews`)
  - Controller: `App\Http\Controllers\API\ProfileController@reviews`
  - Menampilkan reviews yang dibuat user
  
- **Store Review** (`POST /api/user/store-review`)
  - Controller: `App\Http\Controllers\API\ProfileController@store_review`
  - Membuat review untuk mobil

##### F. Transactions
- **Transactions** (`GET /api/user/transactions`)
  - Controller: `App\Http\Controllers\API\ProfileController@transactions`
  - Menampilkan subscription history

##### G. Dealers
- **List Dealers** (`GET /api/dealers`)
  - Controller: `App\Http\Controllers\API\HomeController@dealers`
  - Filter: search, location
  
- **Dealer Detail** (`GET /api/dealer/{slug}`)
  - Controller: `App\Http\Controllers\API\HomeController@dealer`
  
- **Send Message to Dealer** (`POST /api/send-message-to-dealer/{id}`)
  - Controller: `App\Http\Controllers\API\HomeController@send_message_to_dealer`

---

### 2. DEALER/SELLER
**Identifikasi:** `is_dealer = 1` dan `email_verified_at != null`

#### Fungsi Tambahan Selain Regular User:

##### A. Upgrade ke Dealer
- **Seller Register** (`POST /api/seller/store-register`)
  - Controller: `App\Http\Controllers\API\Auth\RegisterController@seller_store_register`
  - **CATATAN:** Method ini direferensikan di routes tapi belum ada implementasinya
  
- **Upgrade via Payment** (`POST /api/user/payment`)
  - Controller: `App\Http\Controllers\API\PaymentController@payment`
  - Setelah payment subscription berhasil, `is_dealer` di-set menjadi `1`
  - Location: `app/Http/Controllers/API/PaymentController.php:539`

##### B. Dashboard Dealer
- **Dashboard** (`GET /api/user/dashboard`)
  - Controller: `App\Http\Controllers\API\ProfileController@dashboard`
  - Menampilkan cars yang dimiliki dealer (via `agent_id`)
  - Return type: `user_type: 'dealer'` saat login

##### C. Booking Management
- **Booking Request** (`GET /api/user/booking-request`)
  - Controller: `App\Http\Controllers\API\ProfileController@bookingRequest`
  - Menampilkan booking requests untuk dealer
  - Filter: `supplier_id = user->id`
  
- **Booking Request Details** (`GET /api/user/booking-request-details/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@bookingRequestDetails`
  
- **Accept Booking** (`POST /api/user/booking-request-accept/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@bookingRequestAccept`
  - Status: `0` (pending) → `1` (approved)
  
- **Cancel Booking (Dealer)** (`POST /api/user/booking-cancel-by-dealer/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@bookingCancelByDealer`
  - Status: `0` atau `1` → `4` (cancelled by dealer)
  
- **Complete Booking** (`POST /api/user/booking-complete-by-dealer/{id}`)
  - Controller: `App\Http\Controllers\API\ProfileController@bookingCompleteByDealer`
  - Status: `6` (ride started) → `2` (completed)

##### D. Car Management (via Web Admin atau Module)
- Dealer dapat membuat listing mobil melalui:
  - Module: `Modules\Car\Entities\Car`
  - Field `agent_id` = `user->id`
  - Approval: `approved_by_admin` harus 'approved'

##### E. Withdraw (Pencairan)
- **Withdraw List** (`GET /api/user/withdraw`)
  - Controller: `App\Http\Controllers\API\SellerWithdrawController@index`
  - Menampilkan withdraw history
  
- **Request Withdraw** (`POST /api/user/withdraw-request`)
  - Controller: `App\Http\Controllers\API\SellerWithdrawController@store`
  - Hanya bisa withdraw dari completed bookings (`status = 2`)

##### F. Payment Methods untuk Subscription
- **Pay via Stripe** (`POST /api/user/pay-via-stripe`)
- **Pay via PayPal** (via PaymentController)
- **Pay via Bank** (`POST /api/user/pay-via-bank`)
- **Pay via Razorpay** (`POST /api/user/pay-via-razorpay`)
- **Pay via Flutterwave** (`POST /api/user/pay-via-flutterwave`)
- **Pay via Paystack** (`GET /api/user/pay-via-paystack`)
- **Pay via Mollie** (`GET /api/user/pay-via-mollie`)
- **Pay via Instamojo** (`GET /api/user/pay-via-instamojo`)

---

### 3. ADMIN
**Identifikasi:** Login menggunakan `auth:admin` middleware

#### Fungsi Admin (via Web Panel):
- **User Management**
  - List Users (`GET /admin/user-list`)
  - Pending Users (`GET /admin/pending-user`)
  - Show User (`GET /admin/user-show/{id}`)
  - Update User (`POST /admin/user-update/{id}`)
  - Delete User (`DELETE /admin/user-destroy/{id}`)
  - Change Status (`POST /admin/user-status/{id}`)
  
- **Car Approval**
  - Approve/reject car listings
  - Field: `approved_by_admin` ('approved'/'pending'/'rejected')
  
- **KYC Management**
  - Approve/reject KYC submissions
  - Module: `Modules\Kyc\Entities\KycInformation`

---

### 4. INFLUENCER
**Identifikasi:** `is_influencer = 1`

**CATATAN:** Field `is_influencer` ada di database tapi belum ada implementasi fungsi khusus untuk influencer di API. Kemungkinan digunakan untuk fitur marketing/promosi di masa depan.

---

## Contoh User Per Role

### 1. Regular User (Buyer)
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "username": "john-doe-20240101120000",
  "phone": "+1234567890",
  "address": "123 Main St, City",
  "status": "enable",
  "is_banned": "no",
  "is_dealer": 0,
  "is_influencer": null,
  "kyc_status": "disable",
  "designation": null,
  "email_verified_at": "2024-01-01 12:00:00",
  "image": "uploads/custom-images/user-1.jpg",
  "banner_image": null
}
```

**Akses:**
- ✅ Browse listings
- ✅ Add to wishlist
- ✅ View dealer profiles
- ✅ Send messages to dealers
- ✅ Create reviews
- ✅ View transactions (subscription history)

---

### 2. Dealer/Seller
```json
{
  "id": 2,
  "name": "ABC Car Dealer",
  "email": "dealer@abc.com",
  "username": "abc-car-dealer-20240101120000",
  "phone": "+1234567891",
  "address": "456 Dealer St, City",
  "status": "enable",
  "is_banned": "no",
  "is_dealer": 1,
  "is_influencer": null,
  "kyc_status": "enable",
  "designation": "Car Dealer",
  "email_verified_at": "2024-01-01 12:00:00",
  "image": "uploads/custom-images/dealer-1.jpg",
  "banner_image": "uploads/custom-images/dealer-banner-1.jpg",
  "about_me": "We are a trusted car dealer...",
  "facebook": "https://facebook.com/abcdealer",
  "linkedin": "https://linkedin.com/company/abcdealer",
  "twitter": "https://twitter.com/abcdealer",
  "instagram": "https://instagram.com/abcdealer",
  "google_map": "<iframe>...</iframe>",
  "sunday": "09:00-18:00",
  "monday": "09:00-18:00",
  "tuesday": "09:00-18:00",
  "wednesday": "09:00-18:00",
  "thursday": "09:00-18:00",
  "friday": "09:00-18:00",
  "saturday": "09:00-18:00"
}
```

**Akses:**
- ✅ Semua akses Regular User
- ✅ Create car listings (via `agent_id`)
- ✅ Manage bookings (accept/cancel/complete)
- ✅ View booking requests
- ✅ Request withdraw
- ✅ Manage subscription plans
- ✅ View dealer dashboard dengan statistik

---

### 3. Admin
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "image": "uploads/custom-images/admin-1.jpg",
  "designation": "Super Admin",
  "email_verified_at": "2024-01-01 12:00:00"
}
```

**Akses:**
- ✅ Full access ke admin panel
- ✅ Manage users (CRUD)
- ✅ Approve/reject car listings
- ✅ Manage KYC submissions
- ✅ Manage settings
- ✅ View reports

---

## Integrasi Mobile App dengan Web App

### Authentication System
**Vendor:** `tymon/jwt-auth` (JWT Authentication)

#### Flow Authentication:
1. **Login** → `POST /api/store-login`
   - Controller: `App\Http\Controllers\API\Auth\LoginController@store_login`
   - Middleware: `guest:api`
   - Return JWT token dengan structure:
     ```json
     {
       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
       "token_type": "bearer",
       "expires_in": 43200,
       "user": {
         "id": 1,
         "username": "john-doe",
         "name": "John Doe",
         "image": "...",
         "status": "enable",
         "is_banned": "no",
         "is_dealer": 0,
         "designation": null,
         "address": "...",
         "phone": "...",
         "kyc_status": "disable"
       },
       "user_type": "user" // atau "dealer"
     }
     ```

2. **Protected Routes** → Semua route dengan prefix `/api/user/*`
   - Middleware: `auth:api`
   - Header: `Authorization: Bearer {access_token}`

3. **Logout** → `GET /api/user-logout`
   - Controller: `App\Http\Controllers\API\Auth\LoginController@userLogout`
   - Middleware: `auth:api`

---

### API Endpoints untuk Mobile App

#### Public Endpoints (Tidak Perlu Auth):
```
GET  /api/website-setup              - Setup awal (language, currency, settings)
GET  /api/                           - Home page data
GET  /api/all-brands                 - List semua brands
GET  /api/listings-filter-option     - Filter options untuk listings
GET  /api/listings                   - List mobil dengan filter
GET  /api/listing/{id}               - Detail mobil
GET  /api/terms-conditions            - Terms & conditions
GET  /api/privacy-policy             - Privacy policy
GET  /api/dealers                    - List dealers
GET  /api/dealers-filter-option      - Filter options untuk dealers
GET  /api/dealer/{slug}              - Detail dealer
POST /api/send-message-to-dealer/{id} - Kirim pesan ke dealer
GET  /api/join-as-dealer             - Redirect ke KYC
GET  /api/language-switcher           - Switch language
GET  /api/currency-switcher          - Switch currency
GET  /api/cities-by-country/{id}     - List cities by country

POST /api/store-login                - Login
POST /api/store-register              - Register user biasa
POST /api/seller/store-register      - Register seller (BELUM ADA IMPLEMENTASI)
POST /api/resend-register            - Resend OTP
POST /api/user-verification          - Verify email dengan OTP
POST /api/send-forget-password       - Request reset password
POST /api/verify-forget-password-otp - Verify OTP reset password
POST /api/store-reset-password       - Reset password
GET  /api/user-logout                - Logout
```

#### Protected Endpoints (Perlu Auth: `auth:api`):
```
GET    /api/user/dashboard                    - Dashboard user/dealer
GET    /api/user/edit-profile                 - Get profile data
PUT    /api/user/update-profile               - Update profile
GET    /api/user/change-password              - Get change password form
POST   /api/user/update-password              - Update password
POST   /api/user/upload-user-avatar           - Upload avatar
GET    /api/user/transactions                 - Subscription history
GET    /api/user/wishlists                    - List wishlist
GET    /api/user/add-to-wishlist/{id}         - Add to wishlist
DELETE /api/user/remove-wishlist/{id}         - Remove from wishlist
GET    /api/user/reviews                      - List reviews user
POST   /api/user/store-review                 - Create review

POST   /api/user/payment                      - Calculate payment
POST   /api/user/pay-via-stripe               - Payment via Stripe
POST   /api/user/pay-via-bank                 - Payment via Bank Transfer
POST   /api/user/pay-via-razorpay            - Payment via Razorpay
POST   /api/user/pay-via-flutterwave          - Payment via Flutterwave
GET    /api/user/pay-via-paystack             - Payment via Paystack
GET    /api/user/pay-via-mollie               - Payment via Mollie
GET    /api/user/mollie-payment-success       - Mollie callback
GET    /api/user/pay-via-instamojo            - Payment via Instamojo
GET    /api/user/response-instamojo           - Instamojo callback
```

#### Dealer-Specific Endpoints (Perlu `is_dealer = 1`):
```
GET  /api/user/booking-request                - List booking requests
GET  /api/user/booking-request-details/{id}   - Detail booking request
POST /api/user/booking-request-accept/{id}    - Accept booking
POST /api/user/booking-cancel-by-dealer/{id}  - Cancel booking (dealer)
POST /api/user/booking-complete-by-dealer/{id} - Complete booking
GET  /api/user/withdraw                        - Withdraw list
POST /api/user/withdraw-request                - Request withdraw
```

---

### Middleware yang Digunakan

1. **`HtmlSpecialchars`**
   - Location: `app/Http/Middleware/HtmlSpecialchars.php` (kemungkinan)
   - Digunakan untuk sanitize input

2. **`CurrencyLangaugeForAPI`**
   - Location: `app/Http/Middleware/CurrencyLangaugeForAPI.php` (kemungkinan)
   - Set currency dan language untuk API

3. **`auth:api`**
   - Vendor: `tymon/jwt-auth`
   - Validasi JWT token

---

### Response Format

#### Success Response:
```json
{
  "message": "Success message",
  "data": { ... }
}
```

#### Error Response:
```json
{
  "message": "Error message"
}
```
Status code: `403` untuk validation errors, `401` untuk unauthorized

---

## Function dari Vendor yang Digunakan

### ⚠️ CATATAN PENTING: QuomodoSoft & Library Mobile App
**Status:** Tidak ditemukan library atau package QuomodoSoft di folder `vendor/`
- QuomodoSoft hanya muncul sebagai **copyright text** di:
  - `database/seeders/SettingTranslationsSeeder.php` (line 22, 29)
  - `storage/logs/laravel.log` (sebagai copyright dalam setting)
- Kemungkinan QuomodoSoft adalah **nama vendor/company** dari template/theme yang digunakan, bukan library khusus

**Library untuk Komunikasi Mobile App:**
- ✅ **JWT Auth** (`tymon/jwt-auth`) - Library utama untuk authentication mobile
- ✅ **Laravel Sanctum** - API tokens (secondary)
- ✅ **Custom Middleware** - `CurrencyLangaugeForAPI`, `HtmlSpecialchars`
- ✅ **Guzzle HTTP** - HTTP client untuk external API calls

**Detail lengkap:** Lihat `DOKUMENTASI_LIBRARY_MOBILE_APP.md`

**Rekomendasi:** 
- Jika ada library QuomodoSoft yang terlihat, mohon berikan path lengkap
- Semua library untuk mobile app communication sudah didokumentasikan di file terpisah

---

### 1. JWT Authentication
**Vendor:** `tymon/jwt-auth`
**Package:** `tymon/jwt-auth`

#### Functions yang Digunakan:
- `Auth::guard('api')->attempt($credentials)` - Login attempt
- `Auth::guard('api')->user()` - Get current authenticated user
- `Auth::guard('api')->logout()` - Logout
- `auth('api')->factory()->getTTL()` - Get token TTL

**Location:**
- `app/Http/Controllers/API/Auth/LoginController.php`
- `app/Models/User.php` (implements `JWTSubject`)

---

### 2. Laravel Sanctum
**Vendor:** `laravel/sanctum`
**Package:** `Laravel\Sanctum\HasApiTokens`

#### Functions yang Digunakan:
- Trait `HasApiTokens` pada model `User` dan `Admin`

**Location:**
- `app/Models/User.php`
- `app/Models/Admin.php`

---

### 3. Payment Gateways

#### A. Stripe
**Vendor:** `stripe/stripe-php`
**Package:** `Stripe\Stripe`

**Functions:**
- `Stripe::setApiKey()` - Set API key
- `Charge::create()` - Create charge

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

#### B. PayPal
**Vendor:** PayPal SDK
**Package:** PayPal SDK

**Functions:**
- PayPal API calls untuk payment processing

**Location:**
- `app/Http/Controllers/API/PaymentController.php`
- `app/Http/Controllers/API/PaypalController.php`

#### C. Razorpay
**Vendor:** `razorpay/razorpay`
**Package:** `Razorpay\Api\Api`

**Functions:**
- `Api::paymentLink()->create()` - Create payment link
- `Api::paymentLink()->fetch()` - Fetch payment link

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

#### D. Flutterwave
**Vendor:** Flutterwave SDK

**Functions:**
- Flutterwave API calls

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

#### E. Paystack
**Vendor:** Paystack SDK

**Functions:**
- Paystack API calls

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

#### F. Mollie
**Vendor:** `mollie/laravel-mollie`
**Package:** `Mollie\Laravel\Facades\Mollie`

**Functions:**
- `Mollie::api()->payments()->create()` - Create payment
- `Mollie::api()->payments()->get()` - Get payment

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

#### G. Instamojo
**Vendor:** Instamojo SDK

**Functions:**
- Instamojo API calls

**Location:**
- `app/Http/Controllers/API/PaymentController.php`

---

### 4. Social Login
**Vendor:** `laravel/socialite`
**Package:** `Laravel\Socialite\Facades\Socialite`

#### Functions yang Digunakan:
- `Socialite::driver('google')->redirect()` - Redirect ke Google
- `Socialite::driver('google')->user()` - Get Google user
- `Socialite::driver('facebook')->redirect()` - Redirect ke Facebook
- `Socialite::driver('facebook')->user()` - Get Facebook user

**Location:**
- `app/Http/Controllers/API/Auth/LoginController.php`

**CATATAN:** Social login saat ini hanya untuk web, belum terintegrasi dengan mobile app API.

---

### 5. Image Processing
**Vendor:** `intervention/image` (kemungkinan)
**Package:** `Image`

#### Functions yang Digunakan:
- `Image::make()` - Create image instance
- `uploadFile()` - Helper function untuk upload file

**Location:**
- `app/Http/Controllers/API/ProfileController.php`
- Helper functions (kemungkinan di `app/Helpers/`)

---

### 6. Email
**Vendor:** Laravel Mail
**Package:** `Illuminate\Support\Facades\Mail`

#### Functions yang Digunakan:
- `Mail::to($email)->send($mailable)` - Send email
- `MailHelper::setMailConfig()` - Set mail configuration

**Location:**
- `app/Http/Controllers/API/Auth/LoginController.php`
- `app/Http/Controllers/API/Auth/RegisterController.php`
- `app/Helpers/MailHelper.php` (kemungkinan)

---

### 7. Modules System
**Vendor:** `nwidart/laravel-modules`
**Package:** Laravel Modules

#### Modules yang Digunakan:
- `Modules\Car` - Car management
- `Modules\Brand` - Brand management
- `Modules\City` - City management
- `Modules\Country` - Country management
- `Modules\Currency` - Currency management
- `Modules\Language` - Language management
- `Modules\Subscription` - Subscription plans
- `Modules\Kyc` - KYC management
- `Modules\GeneralSetting` - General settings
- `Modules\Page` - Page management
- `Modules\Slider` - Slider management
- `Modules\BannerSlider` - Banner slider
- `Modules\Feature` - Car features
- `Modules\ContactMessage` - Contact messages
- `Modules\Newsletter` - Newsletter
- `Modules\Testimonial` - Testimonials
- `Modules\Blog` - Blog
- `Modules\CompareList` - Compare list
- `Modules\Menu` - Menu management

---

## Catatan Penting untuk Modifikasi

### 1. Missing Implementation
- **`seller_store_register`** method direferensikan di `routes/api.php:56` tapi tidak ada implementasinya di `RegisterController`. Perlu dibuat atau dihapus dari routes.

### 2. Booking Model
- Model `Booking` digunakan di beberapa controller tapi file model tidak ditemukan. Perlu dicek apakah:
  - Model ada di lokasi lain
  - Atau perlu dibuat migration dan model baru

### 3. KYC Flow
- KYC module ada (`Modules\Kyc`) tapi flow lengkapnya perlu dicek untuk upgrade user menjadi dealer.

### 4. Subscription Flow
- User menjadi dealer setelah payment subscription berhasil
- Field `is_dealer` di-set ke `1` di: `app/Http/Controllers/API/PaymentController.php:539`

### 5. Car Listing
- Dealer membuat listing via `agent_id` field di tabel `cars`
- Approval dilakukan admin via `approved_by_admin` field

---

## Rekomendasi untuk Business Flow Modifikasi

1. **Review semua routes** di `routes/api.php` dan pastikan semua controller method ada
2. **Buat Booking model** jika belum ada
3. **Implementasikan seller_store_register** atau hapus dari routes
4. **Dokumentasikan KYC flow** lengkap
5. **Review payment flow** untuk memastikan semua payment gateway terintegrasi dengan benar
6. **Buat API documentation** lengkap dengan Postman collection atau Swagger
7. **Implementasikan role-based access control** yang lebih granular jika diperlukan

---

**Dokumen ini dibuat berdasarkan analisis source code pada:** `C:\Grafik\UMAR\ecomotif\mainv2\main_files`

**Tanggal:** 2025-01-XX

