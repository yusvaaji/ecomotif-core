# Dokumentasi API (Web App)

Dokumen ini dibuat dari definisi route di codebase (Laravel + modul `nwidart/laravel-modules`). **Base path:** semua URL di bawah ini relatif terhadap `{APP_URL}` (misalnya `https://domain-anda.com`).

**Prefix umum:** `/api`

**OpenAPI & Swagger UI (HTML):**

- Spesifikasi: `docs/openapi.yaml` — dilayani sebagai `GET /docs/openapi.yaml` (YAML).
- UI interaktif: `GET /api-docs` (memuat Swagger UI dari CDN; pastikan aplikasi dapat diakses dari browser).

**Autentikasi:**

- Route bertanda **JWT** memakai guard `api` (driver `jwt` di `config/auth.php`). Kirim header: `Authorization: Bearer <access_token>`.
- Beberapa route modul memakai **Sanctum** (`auth:sanctum`) — lihat bagian terpisah.

**Middleware tambahan (ringkas):**

- Banyak route publik & user: `HtmlSpecialchars`, `CurrencyLangaugeForAPI`, atau kombinasi `XSS`, `DEMO`, dll. sesufile route modul.
- Route role: `mediator`, `showroom`, `marketing` (ditumpuk dengan `auth:api`).

**Kolom Parameter:** path, query, body JSON, atau multipart; `-` = tidak ada; *(opsional)* = tidak wajib validasi.

---

## 1. Publik & home (`routes/api.php`)

| Method | Endpoint | Keterangan | Parameter |
|--------|----------|------------|-----------|
| GET | `/api/website-setup` | Setup website | Query: `lang_code` *(opsional)* |
| GET | `/api/` | Home / index | - |
| GET | `/api/all-brands` | Daftar merek | - |
| GET | `/api/listings-filter-option` | Opsi filter listing | Query: `country` *(opsional)* — filter kota |
| GET | `/api/listings` | Daftar listing | Query: `country_id`, `location`, `brands[]`, `condition[]`, `purpose[]`, `search`, `showroom_id` |
| GET | `/api/listing/{id}` | Detail listing | Path: `id` mobil |
| GET | `/api/terms-conditions` | Syarat & ketentuan | - |
| GET | `/api/privacy-policy` | Kebijakan privasi | - |
| GET | `/api/dealers` | Dealer | Query: `search`, `location`, `min_rating`, `lat`, `lng`, `radius_km` *(default 25)* |
| GET | `/api/dealers-filter-option` | Opsi filter dealer | - |
| GET | `/api/dealer/{slug}` | Detail dealer | Path: slug/username dealer |
| GET | `/api/showrooms` | Showroom (select) | Query: `search`, `location`, `min_rating`, `lat`, `lng`, `radius_km` |
| GET | `/api/join-as-dealer` | Gabung dealer | - (redirect web) |
| GET | `/api/language-switcher` | Pengalih bahasa | Query: `lang_code` — response redirect |
| GET | `/api/currency-switcher` | Pengalih mata uang | Query: `currency_code` — redirect |
| GET | `/api/cities-by-country/{id}` | Kota per negara | Path: `country_id` |
| POST | `/api/send-message-to-dealer/{id}` | Kirim pesan ke dealer | Path: id dealer. Body: `name`, `email`, `subject`, `message`, `g-recaptcha-response`; `phone` dipakai template |
| POST | `/api/calculate-installment` | Hitung cicilan | `car_price`, `down_payment`; `tenure_months`, `interest_rate` *(opsional)* |
| POST | `/api/calculate-payment-capability` | Kemampuan bayar | `monthly_income`, `monthly_expenses`, `existing_loans`, `car_price`; `tenure_months` *(opsional)* |
| POST | `/api/calculator/payment-capability` | Calculator | Sama seperti atas |
| POST | `/api/scan-showroom/{code}` | Scan barcode | Path: `code` = `barcode` dealer |
| GET | `/api/garages` | Daftar bengkel | Query: `search`, `location`, `min_rating`, `lat`, `lng`, `radius_km` |
| GET | `/api/garages/{id}` | Detail bengkel | Path: id user bengkel |
| GET | `/api/communities` | Daftar komunitas | Query: `search`, `location` |
| GET | `/api/communities/{slug}` | Detail komunitas | Path: `slug` |

---

## 2. Auth & akun (tanpa JWT / sebelum sesi API)

| Method | Endpoint | Keterangan | Parameter |
|--------|----------|------------|---------|
| POST | `/api/store-login` | Login | `email`, `password`, `g-recaptcha-response` |
| POST | `/api/store-register` | Registrasi | `name`, `email`, `password`, `password_confirmation` |
| POST | `/api/seller/store-register` | Registrasi seller | `name`, `email`, `phone`, `address`, `password`, `password_confirmation` |
| POST | `/api/garage/store-register` | Registrasi bengkel | `name`, `email`, `phone`, `address`, `password`, `password_confirmation`; `specialization`, `latitude`, `longitude` *(opsional)* |
| POST | `/api/mediator/store-register` | Registrasi mediator | `name`, `email`, `phone`, `address`, `password`, `password_confirmation`; `showroom_id` *(opsional)* |
| POST | `/api/resend-register` | Kirim ulang OTP | `email` |
| POST | `/api/user-verification` | Verifikasi email | `email`, `otp` |
| POST | `/api/send-forget-password` | Lupa password | `email`, `g-recaptcha-response` |
| POST | `/api/verify-forget-password-otp` | Verifikasi OTP reset | `email`, `otp` |
| POST | `/api/store-reset-password` | Password baru | `email`, `password`, `password_confirmation`, `otp`, `g-recaptcha-response` |
| GET | `/api/user-logout` | Logout | Header: Bearer JWT |

---

## 3. User terautentikasi — prefix `/api/user` (JWT: `auth:api`)

### 3.1 Pembayaran (`App\Http\Controllers\API\PaymentController`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| POST | `/api/user/payment` | JSON: `car_id`, `pickup_location`, `return_location` (kota id), `pickup_date`, `return_date` (`Y-m-d`), `number_of_day` |
| POST | `/api/user/pay-via-stripe` | Body saat ini: `stripeToken` (implementasi controller menunjukkan signature dengan `$id` plan, namun route tidak memuat `{id}`) |
| POST | `/api/user/pay-via-bank` | Body: `tnx_info` (controller juga mengindikasikan membutuhkan `$id` plan) |
| POST | `/api/user/pay-via-razorpay` | `razorpay_payment_id`, payload gateway |
| POST | `/api/user/pay-via-flutterwave` | `tnx_id` |
| GET | `/api/user/pay-via-paystack` | Query: `reference`, `tnx_id` |
| GET | `/api/user/pay-via-mollie` | Sesi / redirect web |
| GET | `/api/user/mollie-payment-success` | Sesi |
| GET | `/api/user/pay-via-instamojo` | Sesi |
| GET | `/api/user/response-instamojo` | Query: `payment_id` |

**Endpoint langganan yang konsisten untuk API mobile:** `POST /api/user/pay-with-bank/{id}`, `POST /api/user/pay-with-stripe/{id}` (`Modules/Subscription`) — detail di §6.

### 3.2 Profil & wishlist (`ProfileController`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/dashboard` | - |
| GET | `/api/user/edit-profile` | - |
| PUT | `/api/user/update-profile` | `name`, `email`, `phone`, `address` (wajib); `designation`, `date_of_birth`, `gender`, `latitude`, `longitude`; file `image`, `banner_image` |
| GET | `/api/user/change-password` | Tanpa parameter body/query (route terdaftar; umumnya endpoint form/change-password) |
| POST | `/api/user/update-password` | `current_password`, `password`, `password_confirmation` |
| POST | `/api/user/upload-user-avatar` | Route ada; referensi web: multipart `image` (jpeg/png/jpg max 1MB) |
| GET | `/api/user/transactions` | - |
| GET | `/api/user/wishlists` | - |
| GET | `/api/user/add-to-wishlist/{id}` | Path: `car_id` |
| DELETE | `/api/user/remove-wishlist/{id}` | Path: `car_id` |
| GET | `/api/user/reviews` | - |
| POST | `/api/user/store-review` | `rating`, `comment`, `car_id`, `g-recaptcha-response` |

### 3.3 Mediator — prefix `/api/user/mediator`

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/mediator/dashboard` | - |
| GET | `/api/user/mediator/applications` | Query: `leasing_status`, `application_type` |
| POST | `/api/user/mediator/applications` | `consumer_name`, `consumer_email`, `consumer_phone`, `car_id`, `down_payment`, `installment_amount`; `showroom_id` opsional |
| GET | `/api/user/mediator/applications/{id}` | Path: booking id |
| PUT | `/api/user/mediator/applications/{id}` | JSON: `down_payment`, `installment_amount`, `showroom_id` (jika masih pending) |

### 3.4 Showroom — prefix `/api/user/showroom`

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| POST | `/api/user/showroom/generate-barcode` | - |
| GET | `/api/user/showroom/barcode` | - |
| GET | `/api/user/showroom/applications` | Query: `status`, `leasing_status`, `application_type`, `source` (`direct`/`mediator`/`marketing`) |
| GET | `/api/user/showroom/applications/{id}` | Path |
| POST | `/api/user/showroom/applications/{id}/review` | Path; body kosong |
| POST | `/api/user/showroom/applications/{id}/pool-to-leasing` | Path |
| GET | `/api/user/showroom/applications/{id}/leasing-result` | Path |
| POST | `/api/user/showroom/applications/{id}/appeal` | `reason` (wajib) |
| POST | `/api/user/showroom/applications/{id}/handle-dp` | Path |

### 3.5 Marketing — prefix `/api/user/marketing`

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/marketing/dashboard` | - |
| GET | `/api/user/marketing/applications` | Query: `leasing_status` |
| POST | `/api/user/marketing/applications` | `consumer_name`, `consumer_email`, `consumer_phone`, `car_id`, `down_payment`, `installment_amount` |

### 3.6 Pengajuan konsumen (`ApplicationController`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/applications` | Query: `status`, `leasing_status`, `application_type`, `per_page` |
| POST | `/api/user/applications` | `car_id`, `down_payment`, `installment_amount`; `showroom_id` opsional |
| POST | `/api/user/applications/{id}/documents` | Multipart: `documents[]` (pdf/jpg/jpeg/png, max 5MB) |
| GET | `/api/user/applications/{id}` | Path |
| POST | `/api/user/applications/{id}/pay-dp` | Path: `id`; body belum dipakai di controller saat ini |

### 3.7 Service Booking konsumen (`GarageController`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/service-bookings` | Query: `status` |
| POST | `/api/user/service-bookings` | `garage_id`, `garage_service_id`, `service_type`, `booking_date`, `booking_time`, `customer_name`, `customer_phone`; `customer_address` wajib jika `home_service`; `vehicle_brand`, `vehicle_model`, `vehicle_year`, `vehicle_plate`, `notes` opsional |
| GET | `/api/user/service-bookings/{id}` | Path |
| POST | `/api/user/service-bookings/{id}/cancel` | Path |

### 3.8 Garage owner — prefix `/api/user/garage`

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/garage/dashboard` | - |
| GET | `/api/user/garage/services` | - |
| POST | `/api/user/garage/services` | Multipart: `name`, `price` wajib; `description`, `duration`, `image` |
| PUT | `/api/user/garage/services/{id}` | `name`, `description`, `price`, `duration`, `image`, `status` (`active`/`inactive`) |
| DELETE | `/api/user/garage/services/{id}` | Path |
| GET | `/api/user/garage/bookings` | Query: `status`, `service_type` |
| GET | `/api/user/garage/bookings/{id}` | Path |
| PUT | `/api/user/garage/bookings/{id}/status` | Path: `id`; JSON: `status` (`confirmed`/`in_progress`/`completed`/`cancelled`), `garage_notes` *(opsional)* |

### 3.9 Komunitas (`CommunityController`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/my-communities` | - |
| POST | `/api/user/communities` | Multipart: `name` wajib; `description`, `image`, `cover_image`, `location`, `privacy` |
| POST | `/api/user/communities/{slug}/join` | Path |
| POST | `/api/user/communities/{slug}/leave` | Path |
| GET | `/api/user/communities/{slug}/members` | Path |
| GET | `/api/user/communities/{slug}/posts` | Path |
| POST | `/api/user/communities/{slug}/posts` | `content` wajib; `image` opsional |
| POST | `/api/user/community-posts/{postId}/comments` | `content` |

### 3.10 Wallet & Notifikasi

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/wallet` | - |
| GET | `/api/user/wallet/transactions` | Query: `type` (`credit`/`debit`) |
| GET | `/api/user/notifications` | Query: `is_read` |
| POST | `/api/user/notifications/{id}/read` | Path |
| POST | `/api/user/notifications/read-all` | - |

---

## 4. Modul: mobil penjual (`Modules/Car/Routes/api.php`)

Prefix: `/api/user` — JWT + middleware grup `XSS`, `DEMO`, `CurrencyLangaugeForAPI`, `HtmlSpecialchars`.

| Method | Endpoint | Catatan | Parameter |
|--------|----------|---------|-----------|
| GET | `/api/user/select-car-purpose` | Bisa HTML | - |
| GET | `/api/user/car` | index | Pagination Laravel |
| POST | `/api/user/car` | `CarBasicRequest` | `brand_id`, `title`, `slug` (unique), `description`, `condition` (`used`/`new`), `purpose` (`Rent`/`Sale`), `regular_price`, `offer_price` opsional; `seo_title`, `seo_description` opsional |
| GET | `/api/user/car/create` | Kuota langganan | - |
| GET | `/api/user/car/{car}` | show | Path |
| GET | `/api/user/car/{car}/edit` | edit | Path + Query: `lang_code` |
| PUT/PATCH | `/api/user/car/{car}` | `CarBasicRequest` | Jika `lang_code` = admin: `brand_id`, `condition`, `regular_price`, `offer_price`, `translate_id`, `title`, `description`, SEO; else `title`, `description` |
| DELETE | `/api/user/car/{car}` | destroy | Path |
| POST | `/api/user/car-key-feature/{id}` | | `seller_type`, `body_type`, `engine_size`, `interior_color`, `exterior_color`, `year`, `mileage`, `number_of_owner`, `fuel_type`, `transmission`, `drive` |
| POST | `/api/user/car-feature/{id}` | | `features` (array) |
| POST | `/api/user/car-address/{id}` | | `city_id`, `country_id`, `address`, `google_map` |
| POST | `/api/user/video-images/{id}` | | `video_id`, `video_description`, `thumb_image`, `video_image`, `file[]` (multipart gallery) |
| DELETE | `/api/user/image-delete/{id}` | | Path: id `CarGallery` |
| POST | `/api/user/request-to-publish/{id}` | | Path |
| GET | `/api/user/car-gallery/{id}` | | Path: `car_id`; parameter body/query tidak ada |
| POST | `/api/user/upload-gallery/{id}` | | Multipart `file[]` |
| DELETE | `/api/user/delete-gallery/{id}` | | Path id galeri |

---

## 5. Modul: perbandingan (`Modules/CompareList/Routes/api.php`)

`Route::resource('comparelist', ...)` di `/api/user`:

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/comparelist` | - |
| POST | `/api/user/comparelist` | `car_id` (wajib) |
| GET | `/api/user/comparelist/create` | - |
| GET | `/api/user/comparelist/{comparelist}` | Path |
| GET | `/api/user/comparelist/{comparelist}/edit` | Path |
| PUT/PATCH | `/api/user/comparelist/{comparelist}` | Cek controller |
| DELETE | `/api/user/comparelist/{comparelist}` | **Nilai URL di controller dipakai sebagai `car_id`** (bukan PK compare list) |

---

## 6. Modul: langganan / pembayaran (`Modules/Subscription/Routes/api.php`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/pricing-plan` | - |
| GET | `/api/user/free-enroll/{id}` | Path: plan id (harga 0) |
| GET | `/api/user/payment-info` | - |
| POST | `/api/user/pay-with-bank/{id}` | Path: plan id. Body: `tnx_info` |
| POST | `/api/user/pay-with-stripe/{id}` | Path: plan id. Body: `card_number`, `year`, `month`, `cvc` |

---

## 7. Modul: KYC (`Modules/Kyc/Routes/api.php`)

| Method | Endpoint | Parameter |
|--------|----------|-----------|
| GET | `/api/user/kyc` | - |
| POST | `/api/user/kyc-submit` | `kyc_id`, `file` (upload), `message` |

---

## 8. Modul: pesan kontak (`Modules/ContactMessage/Routes/api.php`)

| Method | Endpoint | Middleware | Parameter |
|--------|----------|------------|-----------|
| POST | `/api/store-contact-message` | `XSS`, `HtmlSpecialchars`, `DEMO` | `name`, `email`, `subject`, `message`, `g-recaptcha-response` |

---

## 9. Modul: negara (`Modules/Country/Routes/api.php`)

| Method | Endpoint | Catatan | Parameter |
|--------|----------|---------|-----------|
| GET | `/api/country` | Mengembalikan `$request->user()` | - |

---

## 10. Modul: Menu & Banner (Sanctum)

Prefix modul: `/api` + `v1`.

| Method | Endpoint | Auth | Parameter |
|--------|----------|------|-----------|
| GET | `/api/v1/menu` | `auth:sanctum` | - |
| GET | `/api/v1/bannerslider` | `auth:sanctum` | - |

> Catatan: handler saat ini mengembalikan `$request->user()` — kemungkinan stub; sesuaikan jika sudah ada implementasi bisnis.

---

## 11. Modul dengan `Routes/api.php` kosong

File berikut tidak mendefinisikan endpoint tambahan (hanya header komentar):  
`Blog`, `Brand`, `City`, `Currency`, `Feature`, `GeneralSetting`, `Language`, `Newsletter`, `Page`, `Slider`, `Testimonial`.

---

## 12. Verifikasi & pemeliharaan

Untuk daftar route yang paling akurat di lingkungan lokal, jalankan:

```bash
php artisan route:list --path=api
```

Jika perintah gagal, perbaiki error aplikasi terlebih dahulu (contoh yang terdeteksi: duplikasi nama class `PasswordResetLinkController` antara namespace `App\Http\Controllers\Auth` dan `App\Http\Controllers\Admin\Auth`).

---

## 13. Batasan dokumentasi ini

- **Response JSON** detail: lihat controller (`app/Http/Controllers/API/`, `Modules/*/Http/Controllers/`).
- **Parameter** di §1–§10 mengikuti validasi / `$request` di kode; jika route dan signature controller tidak cocok, utamakan `php artisan route:list`.
- **Rate limit** default grup `api` Laravel.

---

## 14. Contoh body & query (referensi cepat)

Ringkasan tabel ada di §1–§10; berikut contoh untuk integrasi klien.

### 14.1 Auth & Registrasi

`POST /api/store-login` — tambahkan `g-recaptcha-response` jika validasi Captcha aktif.
```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

`POST /api/store-register`
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "secret",
  "password_confirmation": "secret"
}
```

`POST /api/seller/store-register`
```json
{
  "name": "Showroom Name",
  "email": "showroom@example.com",
  "phone": "08123456789",
  "address": "Alamat showroom",
  "password": "secret",
  "password_confirmation": "secret"
}
```

`POST /api/garage/store-register`
```json
{
  "name": "Bengkel Maju",
  "email": "garage@example.com",
  "phone": "08123456789",
  "address": "Alamat bengkel",
  "specialization": "Service rutin dan tune up",
  "latitude": -6.2,
  "longitude": 106.8,
  "password": "secret",
  "password_confirmation": "secret"
}
```

### 14.2 Listing & Discovery

`GET /api/listings` (query params):
- `country_id`
- `location`
- `brands[]`
- `condition[]`
- `purpose[]`
- `search`
- `showroom_id` (baru, untuk katalog per showroom)

`GET /api/showrooms` (query params):
- `search`
- `location`
- `min_rating`
- `lat`
- `lng`
- `radius_km`

`GET /api/dealers` (query params):
- `search`
- `location`
- `min_rating`
- `lat`
- `lng`
- `radius_km`

### 14.3 Application / Order Showroom

`GET /api/user/applications` (query params):
- `status`
- `leasing_status`
- `application_type`
- `per_page`

`POST /api/user/applications`
```json
{
  "car_id": 10,
  "down_payment": 25000000,
  "installment_amount": 3200000,
  "showroom_id": 5
}
```

`POST /api/user/applications/{id}/documents` (multipart):
- `documents[]` (file pdf/jpg/jpeg/png, max 5MB per file)

### 14.4 Bengkel / Garage

`GET /api/garages` (query params):
- `search`
- `min_rating`
- `lat`
- `lng`
- `radius_km`

`POST /api/user/service-bookings`
```json
{
  "garage_id": 7,
  "garage_service_id": 11,
  "service_type": "walk_in",
  "booking_date": "2026-03-30",
  "booking_time": "10:30",
  "customer_name": "Umar",
  "customer_phone": "08123456789",
  "customer_address": "Alamat customer (wajib jika home_service)",
  "vehicle_brand": "Toyota",
  "vehicle_model": "Avanza",
  "vehicle_year": "2020",
  "vehicle_plate": "B1234CD",
  "notes": "Keluhan mesin bergetar"
}
```

`GET /api/user/service-bookings` (query params):
- `status`

`POST /api/user/garage/services` (multipart):
- `name` (required)
- `description`
- `price` (required)
- `duration`
- `image` (optional)

`PUT /api/user/garage/bookings/{id}/status`
```json
{
  "status": "in_progress",
  "garage_notes": "Mulai pengerjaan"
}
```

### 14.5 Komunitas

`POST /api/user/communities` (multipart):
- `name` (required)
- `description`
- `location`
- `privacy` (`public`/`private`)
- `image` (optional)
- `cover_image` (optional)

`POST /api/user/communities/{slug}/posts` (multipart):
- `content` (required)
- `image` (optional)

`POST /api/user/community-posts/{postId}/comments`
```json
{
  "content": "Komentar saya"
}
```

### 14.6 Wallet & Notifikasi

`GET /api/user/wallet`:
- tanpa body

`GET /api/user/wallet/transactions` (query params):
- `type` (`credit`/`debit`)

`GET /api/user/notifications` (query params):
- `is_read` (`0`/`1`)

`POST /api/user/notifications/{id}/read`:
- tanpa body

`POST /api/user/notifications/read-all`:
- tanpa body

### 14.7 Pembayaran sewa mobil & langganan (API)

`POST /api/user/payment`
```json
{
  "car_id": 1,
  "pickup_location": 10,
  "return_location": 10,
  "pickup_date": "2026-04-01",
  "return_date": "2026-04-05",
  "number_of_day": 5
}
```

`POST /api/user/pay-with-stripe/{id}` (plan subscription)
```json
{
  "card_number": "4242424242424242",
  "year": "2030",
  "month": "12",
  "cvc": "123"
}
```

### 14.8 Mediator & marketing (buat aplikasi leasing)

`POST /api/user/mediator/applications`
```json
{
  "consumer_name": "Budi",
  "consumer_email": "budi@example.com",
  "consumer_phone": "081234567890",
  "car_id": 5,
  "down_payment": 10000000,
  "installment_amount": 2500000,
  "showroom_id": 3
}
```
`showroom_id` opsional (default dari `car.agent_id`).

`POST /api/user/marketing/applications` — body yang sama **tanpa** `showroom_id`; showroom diambil dari akun marketing (`showroom_id` user).

### 14.9 Showroom appeal

`POST /api/user/showroom/applications/{id}/appeal`
```json
{
  "reason": "Dokumen tambahan telah diunggah ulang"
}
```

### 14.10 KYC

`POST /api/user/kyc-submit` (multipart)
- `kyc_id` — ID dari `kyc_types`
- `file` — berkas identitas
- `message` — teks pengantar

### 14.11 Listing mobil dealer — simpan draf (`CarBasicRequest`)

`POST /api/user/car`
```json
{
  "brand_id": 1,
  "title": "Toyota Avanza 2020",
  "slug": "toyota-avanza-2020-unique",
  "description": "Deskripsi lengkap...",
  "condition": "used",
  "purpose": "Sale",
  "regular_price": 250000000,
  "offer_price": 245000000,
  "seo_title": "Avanza 2020",
  "seo_description": "Mobil keluarga"
}
```
`condition`: `used` atau `new`; `purpose`: `Rent` atau `Sale`. Untuk `PUT /api/user/car/{id}` tambahkan `lang_code`, `translate_id`, dan field sesuai aturan di §4.
