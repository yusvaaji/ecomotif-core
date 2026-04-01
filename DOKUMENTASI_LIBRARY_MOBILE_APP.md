# Dokumentasi Library untuk Komunikasi Mobile App

## Overview
Dokumen ini menjelaskan library dan komponen yang digunakan khusus untuk komunikasi antara Web App dan Mobile App.

---

## 🔍 Hasil Pencarian Library Khusus

### ⚠️ CATATAN PENTING
**Tidak ditemukan library khusus "QuomodoSoft" di folder `vendor/`**
- Pencarian menyeluruh di seluruh codebase tidak menemukan library dengan nama QuomodoSoft
- QuomodoSoft hanya muncul sebagai **copyright text** di seeder dan setting
- Kemungkinan besar QuomodoSoft adalah **nama vendor/company** dari template/theme yang digunakan

---

## 📚 Library yang Digunakan untuk Mobile App Communication

### 1. JWT Authentication (`tymon/jwt-auth`)
**Vendor:** `tymon/jwt-auth`  
**Version:** `^2.0`  
**Location:** `vendor/tymon/jwt-auth/`

#### Deskripsi:
Library utama untuk authentication mobile app menggunakan JSON Web Token (JWT).

#### Functions yang Digunakan:
```php
// Login attempt
Auth::guard('api')->attempt($credentials)

// Get authenticated user
Auth::guard('api')->user()

// Logout
Auth::guard('api')->logout()

// Get token TTL
auth('api')->factory()->getTTL()
```

#### Implementation:
- **Model:** `app/Models/User.php` implements `JWTSubject`
- **Controller:** `app/Http/Controllers/API/Auth/LoginController.php`
- **Config:** `config/jwt.php`
- **Guard:** `auth:api` middleware

#### Token Structure:
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 43200,
  "user": { ... },
  "user_type": "user" // atau "dealer"
}
```

#### Key Features:
- Token-based authentication untuk stateless API
- Support untuk mobile app (token tidak expire atau bisa di-refresh)
- Custom claims support
- Token blacklist support

---

### 2. Laravel Sanctum (`laravel/sanctum`)
**Vendor:** `laravel/sanctum`  
**Version:** `^3.2`  
**Location:** `vendor/laravel/sanctum/`

#### Deskripsi:
Laravel Sanctum menyediakan sistem authentication ringan untuk SPAs dan simple APIs.

#### Implementation:
- **Trait:** `HasApiTokens` digunakan di:
  - `app/Models/User.php`
  - `app/Models/Admin.php`

#### Usage:
Meskipun Sanctum terinstall, untuk mobile app authentication saat ini menggunakan **JWT Auth** sebagai primary method.

---

### 3. Custom Middleware untuk Mobile API

#### A. CurrencyLangaugeForAPI
**Location:** `app/Http/Middleware/CurrencyLangaugeForAPI.php`

**Fungsi:**
- Set language untuk API request berdasarkan `lang_code` parameter
- Set session `front_lang` untuk localization
- Set application locale

**Usage:**
```php
Route::group(['middleware' => ['CurrencyLangaugeForAPI']], function () {
    // API routes
});
```

**Implementation:**
```php
public function handle(Request $request, Closure $next): Response
{
    $lang_code = 'en';
    
    if($request->lang_code){
        $is_exist = Language::where('lang_code', $request->lang_code)->first();
        if($is_exist){
            $lang_code = $request->lang_code;
        }
    }
    
    Session::put('front_lang', $lang_code);
    app()->setLocale($lang_code);
    
    return $next($request);
}
```

#### B. HtmlSpecialchars
**Location:** `app/Http/Middleware/HtmlSpecialchars.php`

**Fungsi:**
- Sanitize semua input dari request
- Mencegah XSS attacks
- Convert special characters ke HTML entities

**Usage:**
```php
Route::group(['middleware' => ['HtmlSpecialchars']], function () {
    // API routes
});
```

**Implementation:**
```php
public function handle(Request $request, Closure $next): Response
{
    $input = array_filter($request->all());
    
    array_walk_recursive($input, function (&$input) {
        $input = htmlspecialchars($input, ENT_QUOTES);
    });
    
    $request->merge($input);
    
    return $next($request);
}
```

---

### 4. API Controllers Structure

Semua controller untuk mobile app berada di namespace `App\Http\Controllers\API`:

#### Controllers:
- `API\HomeController` - Home, listings, dealers
- `API\ProfileController` - User profile, dashboard, wishlist, reviews
- `API\Auth\LoginController` - Authentication (login, logout, forget password)
- `API\Auth\RegisterController` - Registration
- `API\PaymentController` - Payment processing
- `API\PaypalController` - PayPal payment
- `API\SellerWithdrawController` - Withdraw untuk dealer

#### Response Format:
Semua API endpoints mengembalikan JSON response:
```json
{
  "message": "Success message",
  "data": { ... }
}
```

---

### 5. HTTP Client Libraries

#### A. Guzzle HTTP Client
**Vendor:** `guzzlehttp/guzzle`  
**Version:** `^7.2`  
**Location:** `vendor/guzzlehttp/guzzle/`

**Fungsi:**
- HTTP client untuk external API calls
- Digunakan untuk payment gateway integration
- Support untuk async requests

**Usage:**
Digunakan secara internal oleh payment gateways dan external services.

---

## 🔧 Konfigurasi Mobile API

### Routes Configuration
**File:** `routes/api.php`

**Middleware Stack:**
```php
Route::group(['middleware' => ['HtmlSpecialchars', 'CurrencyLangaugeForAPI']], function () {
    // Public API routes
});

Route::group(['middleware' => ['auth:api']], function () {
    // Protected API routes
});
```

### JWT Configuration
**File:** `config/jwt.php`

**Key Settings:**
- `secret` - JWT secret key (from `.env` JWT_SECRET)
- `ttl` - Token time to live (default: 60 minutes)
- `refresh_ttl` - Refresh token TTL
- `algo` - Algorithm (default: HS256)

**Note untuk Mobile:**
Config menyebutkan bahwa untuk mobile app, token bisa di-set ke `null` untuk never expiring token, tapi tidak direkomendasikan.

---

## 📱 Mobile App Integration Points

### 1. Authentication Flow
```
Mobile App → POST /api/store-login
           → Receive JWT Token
           → Use token in Authorization header
           → Access protected endpoints
```

### 2. Request Headers
```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

### 3. Language & Currency
```
GET /api/website-setup?lang_code=en
GET /api/listings?lang_code=hi&currency_code=USD
```

---

## 🚫 Library yang TIDAK Ditemukan

### QuomodoSoft
- ❌ Tidak ada di `vendor/`
- ❌ Tidak ada di `composer.json`
- ❌ Tidak ada di `composer.lock`
- ❌ Tidak ada namespace `QuomodoSoft\`
- ✅ Hanya muncul sebagai copyright text

**Kemungkinan:**
1. Nama vendor/company dari template/theme
2. Library custom yang belum terinstall
3. Library yang dihapus atau belum ditambahkan ke composer
4. Mungkin ada di folder lain (root, custom packages, dll)

---

## 📋 Daftar Lengkap Dependencies untuk Mobile API

### Dari `composer.json`:
```json
{
  "tymon/jwt-auth": "^2.0",        // JWT Authentication
  "laravel/sanctum": "^3.2",       // API Tokens
  "guzzlehttp/guzzle": "^7.2",     // HTTP Client
  "laravel/framework": "^10.10"    // Laravel Framework
}
```

### Payment Gateways (untuk mobile payment):
- `stripe/stripe-php`: `^12.4`
- `srmklive/paypal`: `^3.0`
- `razorpay/razorpay`: `^2.8`
- `mollie/laravel-mollie`: `^2.25`
- `iyzico/iyzipay-php`: `^2.0`
- `mercadopago/dx-php`: `^2.6`
- `luigel/laravel-paymongo`: `^2.4`

---

## 🔍 Cara Mencari Library Khusus (Jika Ada)

Jika ada library khusus yang belum terdeteksi, coba cek:

1. **Folder vendor dengan nama custom:**
   ```bash
   ls vendor/ | grep -i "custom\|special\|mobile\|api"
   ```

2. **Composer autoload files:**
   ```bash
   cat composer.json | grep -A 10 "autoload"
   ```

3. **Namespace custom di code:**
   ```bash
   grep -r "namespace.*Custom\|use.*Custom" app/
   ```

4. **Custom packages di folder root:**
   ```bash
   ls -la | grep -i "package\|lib\|library"
   ```

---

## 📝 Kesimpulan

**Library utama untuk komunikasi Mobile App:**
1. ✅ **JWT Auth** (`tymon/jwt-auth`) - Authentication
2. ✅ **Laravel Sanctum** - API Tokens (secondary)
3. ✅ **Custom Middleware** - CurrencyLangaugeForAPI, HtmlSpecialchars
4. ✅ **Laravel Framework** - Base framework untuk API

**Tidak ditemukan:**
- ❌ Library khusus "QuomodoSoft"
- ❌ Custom mobile SDK
- ❌ Library khusus untuk mobile communication selain JWT

**Rekomendasi:**
- Jika ada library QuomodoSoft yang terlihat, mohon berikan path lengkap atau lokasi spesifik
- Perlu konfirmasi apakah ada custom package yang belum terdeteksi
- Mungkin library tersebut ada di folder lain atau sebagai git submodule

---

**Dibuat:** 2025-01-XX  
**Berdasarkan:** Analisis source code di `C:\Grafik\UMAR\ecomotif\mainv2\main_files`




