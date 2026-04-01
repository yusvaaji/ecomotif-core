# API vs Figma Mobile App — Coverage Matrix

Dokumen ini memetakan setiap alur (flow) di desain Figma mobile app terhadap endpoint API yang sudah tersedia di backend Laravel. Referensi lengkap endpoint ada di [`docs/API.md`](API.md).

---

## Kamus Istilah Figma → Backend

| Istilah di Figma | Padanan Backend | Endpoint / Model |
|-------------------|-----------------|------------------|
| Produk / Unit | Car listing | `GET /api/listings`, `GET /api/listing/{id}` (model: `Modules\Car\Entities\Car`) |
| Showroom | Dealer (user `is_dealer = 1`) | `GET /api/showrooms`, `GET /api/dealers`, `GET /api/dealer/{slug}` (model: `App\Models\User`) |
| Bengkel / Garage | **Belum ada** | — |
| Order / Pengajuan | Booking (leasing application) | `POST /api/user/applications`, `GET /api/user/applications/{id}` (model: `App\Models\Booking`) |
| Mitra Showroom | Seller/Dealer | `POST /api/seller/store-register`, semua route `/api/user/showroom/*` |
| Mitra Bengkel | **Belum ada** | — |
| Mediator | Mediator user | `POST /api/mediator/store-register`, route `/api/user/mediator/*` |
| Marketing | Marketing user (linked to showroom) | Route `/api/user/marketing/*` |
| Komunitas | **Belum ada** | — |
| Saldo / Wallet | **Belum ada** | — |
| Booking Service | **Belum ada** (Booking saat ini = leasing) | — |
| Katalog (Showroom) | Car listings filtered by `agent_id` | `GET /api/listings` (perlu filter `showroom_id`) |
| Katalog (Bengkel) | **Belum ada** | — |
| Kalkulator Cicilan | ApplicationController | `POST /api/calculate-installment`, `POST /api/calculate-payment-capability` |

---

## Coverage Matrix per Flow Figma

Status legend:
- **Provided** = endpoint sudah ada dan fungsional
- **Partial** = endpoint ada tapi perlu penyesuaian/penambahan field
- **Not Provided** = belum ada endpoint atau modul

---

### Flow 1: Login

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Halaman login (email + password) | Provided | `POST /api/store-login` | — |
| Lupa password | Provided | `POST /api/send-forget-password` | — |
| Verifikasi OTP reset password | Provided | `POST /api/verify-forget-password-otp` | — |
| Simpan password baru | Provided | `POST /api/store-reset-password` | — |

---

### Flow 2: Registrasi Mitra Showroom (multi-step)

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Pilih tipe akun (Pengguna / Mitra) | Partial | `POST /api/store-register` / `POST /api/seller/store-register` | Pemisahan tipe di UI; backend sudah pisah endpoint tapi tidak ada field `account_type` eksplisit |
| Isi profil showroom (nama, alamat, foto) | Partial | `POST /api/seller/store-register` | Hanya name, email, password, phone, address; belum ada foto, kategori, deskripsi |
| Lengkapi profil (peta lokasi, jam operasi) | Not Provided | — | Perlu field `latitude`, `longitude`, `operating_hours` di user/dealer |
| Pilih paket keanggotaan (Rintis/Tumbuh/Mandiri) | Partial | `GET /api/user/pricing-plan` (Subscription module) | Modul ada tapi belum terintegrasi ke alur registrasi mitra |
| Upload bukti bayar paket | Not Provided | — | Perlu endpoint upload bukti transfer + approval admin |
| Disclaimer / persetujuan | Not Provided | — | Perlu field `agreed_terms_at` |
| Verifikasi OTP email | Provided | `POST /api/user-verification` | — |

---

### Flow 3: Registrasi Mitra Bengkel (multi-step)

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Pilih tipe mitra: Bengkel | Not Provided | — | Tidak ada `POST /api/garage/store-register` |
| Isi profil bengkel (nama, alamat, spesialisasi) | Not Provided | — | Modul Garage belum ada |
| Lengkapi profil (peta, jam operasi, layanan) | Not Provided | — | — |
| Pilih paket keanggotaan | Not Provided | — | Perlu integrasi Subscription untuk tipe bengkel |
| Upload bukti bayar | Not Provided | — | — |
| Verifikasi | Provided | `POST /api/user-verification` | OTP reusable |

---

### Flow 4: Customer → Showroom | Order Unit

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Home customer (sapaan, ringkasan) | Partial | `GET /api/` | Response saat ini: sliders, brands, featured cars, banners. Belum ada sapaan user + ringkasan order |
| List showroom + search | Provided | `GET /api/showrooms` | Filter `search`, `location` tersedia |
| Detail showroom (info, peta, rating) | Partial | `GET /api/dealer/{slug}` | Ada info dasar; rating perlu agregasi; peta perlu lat/lng |
| Katalog mobil per showroom | Partial | `GET /api/listings` | Bisa filter `agent_id` tapi tidak ada param `showroom_id` eksplisit |
| Detail produk (spesifikasi, harga) | Provided | `GET /api/listing/{id}` | — |
| Form pemesanan (DP, tenor, metode bayar) | Partial | `POST /api/user/applications` | Field: `car_id`, `down_payment`, `installment_amount`, `showroom_id`. Belum ada: `payment_method` (tunai/leasing), `tenure_years`, data personal lengkap |
| Upload dokumen pengajuan | Provided | `POST /api/user/applications/{id}/documents` | Maks 5MB/file, format pdf/jpg/png |
| Kalkulator cicilan | Provided | `POST /api/calculate-installment` | — |
| Status pengajuan | Provided | `GET /api/user/applications/{id}` | — |
| Bayar DP | Partial | `POST /api/user/applications/{id}/pay-dp` | Implementasi saat ini stub (TODO: payment gateway) |

---

### Flow 5: Customer → Bengkel | Booking Schedule Service

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| List bengkel + search + filter | Not Provided | — | Modul Garage belum ada |
| Detail bengkel (info, peta, layanan, rating) | Not Provided | — | — |
| Pilih layanan (ganti oli, service rutin, dll.) | Not Provided | — | — |
| Booking form (tipe: walk-in / home service) | Not Provided | — | — |
| Pilih tanggal & jam | Not Provided | — | — |
| Isi data kendaraan | Not Provided | — | — |
| Isi data customer (nama, alamat, telepon) | Not Provided | — | — |
| Konfirmasi & submit booking | Not Provided | — | — |

---

### Flow 6: Customer | Order (Riwayat Pesanan)

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Tab Showroom — list riwayat pengajuan | Not Provided | — | Butuh `GET /api/user/applications` (index + pagination + filter status) |
| Tab Bengkel — list riwayat booking service | Not Provided | — | Modul Garage belum ada |
| Detail pesanan + status stepper | Provided | `GET /api/user/applications/{id}` | Hanya untuk showroom/leasing |

---

### Flow 7: Customer | Profile

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Tampilan profil (foto, nama, email, badge) | Provided | `GET /api/user/edit-profile` | — |
| Saldo / wallet | Not Provided | — | Model `Wallet` belum ada |
| Edit profil (nama, email, telepon, alamat, DOB, gender) | Partial | `PUT /api/user/update-profile` | Validasi hanya: name, email, phone, address. DOB & gender belum ada |
| Keamanan (login biometrik, auto logout, ganti email) | Not Provided | — | Perlu endpoint preferensi keamanan |
| Ganti password | Provided | `POST /api/user/update-password` | — |
| Riwayat aktivitas | Not Provided | — | Perlu activity log endpoint |
| Notifikasi | Not Provided | — | Perlu endpoint notifikasi (atau FCM token register) |
| Tampilan (tema gelap/terang) | Not Provided | — | Client-side, tapi bisa simpan preferensi di backend |
| Pusat bantuan | Not Provided | — | Bisa static page atau endpoint FAQ |
| Hubungi kami | Provided | `POST /api/store-contact-message` | — |

---

### Flow 8: Flow Showroom (Dashboard Mitra)

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Home showroom (sapaan, saldo, ringkasan) | Partial | `GET /api/user/dashboard` | Dashboard saat ini return: cars, total_car, total_featured_car, total_wishlist. Belum ada saldo, total order, revenue |
| Tambah katalog (form mobil) | Provided | `POST /api/user/car` + step endpoints | car-key-feature, car-feature, car-address, video-images, upload-gallery |
| List katalog | Provided | `GET /api/user/car` | — |
| List order / pengajuan | Provided | `GET /api/user/showroom/applications` | — |
| Detail pengajuan + dokumen | Provided | `GET /api/user/showroom/applications/{id}` | — |
| Review pengajuan | Provided | `POST /api/user/showroom/applications/{id}/review` | — |
| Pool ke leasing | Provided | `POST /api/user/showroom/applications/{id}/pool-to-leasing` | — |
| Terima hasil leasing | Provided | `GET /api/user/showroom/applications/{id}/leasing-result` | — |
| Banding ke leasing | Provided | `POST /api/user/showroom/applications/{id}/appeal` | — |
| Handle DP | Provided | `POST /api/user/showroom/applications/{id}/handle-dp` | — |
| Generate barcode | Provided | `POST /api/user/showroom/generate-barcode` | — |

---

### Flow 9: Flow Bengkel (Dashboard Mitra)

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| Home bengkel (sapaan, saldo, ringkasan order/service) | Not Provided | — | Modul Garage belum ada |
| Tambah layanan service | Not Provided | — | — |
| List layanan | Not Provided | — | — |
| List order service (walk-in / home) | Not Provided | — | — |
| Detail order service | Not Provided | — | — |
| Status stepper (pending → dikerjakan → selesai) | Not Provided | — | — |
| Upload before/after service | Not Provided | — | — |

---

### Flow 10: Flow Komunitas

| Layar Figma | Status | Endpoint | Catatan |
|-------------|--------|----------|---------|
| List komunitas + search | Not Provided | — | Modul Community belum ada |
| Detail komunitas (anggota, feed, info) | Not Provided | — | — |
| Tambah komunitas (form) | Not Provided | — | — |
| Post aktivitas / feed | Not Provided | — | — |
| Komentar / reply | Not Provided | — | — |
| Join / leave komunitas | Not Provided | — | — |
| Undang anggota | Not Provided | — | — |

---

## Ringkasan Celah (Prioritas) — Diperbarui

| # | Area | Status | Catatan |
|---|------|--------|---------|
| 1 | `GET /api/user/applications` (index) | **DONE** | Ditambahkan dengan filter status/leasing_status |
| 2 | Modul Bengkel / Garage (CRUD + booking) | **DONE** | Model, controller, route, registrasi bengkel lengkap |
| 3 | Modul Komunitas (Community) | **DONE** | CRUD komunitas, membership, post, komentar |
| 4 | Wallet / Saldo (user + mitra) | **DONE** | Model Wallet + WalletTransaction + endpoints |
| 5 | Onboarding mitra multi-step | Partial | Registrasi bengkel ditambahkan; multi-step draft belum |
| 6 | Profil lanjutan (DOB, gender, 2FA, notifikasi) | **DONE** (DOB, gender, notifikasi) | 2FA/biometrik masih client-side |
| 7 | Geo-filter (lat/lng) + rating filter | **DONE** | Haversine di showrooms, dealers, garages |
| 8 | Dashboard mitra (revenue metrics, saldo) | Partial | Dashboard garage ada; revenue belum teragregasi |
| 9 | Showroom-scoped listings | **DONE** | `?showroom_id=` pada `GET /api/listings` |

---

## Web App Coverage (Tambahan)

Status ini khusus implementasi halaman web (Blade) untuk flow yang sebelumnya API-only.

| Area | Status | Route Web | Catatan |
|------|--------|-----------|---------|
| Garage detail + booking form | **DONE** | `/garages/{id}`, `POST /user/service-bookings` | Booking walk-in/home service dari web |
| Service booking history + detail + cancel | **DONE** | `/user/service-bookings`, `/user/service-bookings/{id}`, `POST /user/service-bookings/{id}/cancel` | Riwayat dan aksi cancel |
| Community detail + join/leave + post + comment | **DONE** | `/communities/{slug}`, `POST /user/communities/{slug}/join`, `.../leave`, `.../posts`, `POST /user/community-posts/{id}/comments` | Feed + komentar dari web |
| Create community | **DONE** | `/user/communities/create`, `POST /user/communities` | Form pembuatan komunitas |
| Wallet & notifications pages | **DONE** | `/user/wallet`, `/user/notifications` | Tambahan aksi mark read / mark all read |
| Garage partner dashboard (owner) | **DONE** | `/user/garage/dashboard` | Kelola layanan + update status booking |
| Orders tab Showroom/Bengkel | **DONE** | `/user/orders?tab=showroom|bengkel` | Riwayat order lintas flow dalam satu halaman |
| Garage service management (web owner) | **DONE** | `POST /user/garage/services/{id}/status`, `.../{id}/delete` | Aktivasi/nonaktif + hapus layanan |
| UI polish pages (web) | **DONE** | `/user/orders`, `/user/service-bookings/{id}`, `/garages`, `/communities` | Status badge, card elevation, section hierarchy |
| OpenAPI + Swagger UI | **DONE** | `/docs/openapi.yaml`, `/api-docs` | Dokumen dan UI interaktif |

---

## Referensi

- Endpoint lengkap: [`docs/API.md`](API.md)
- Route utama: `routes/api.php`
- Controller API: `app/Http/Controllers/API/`
- Model Booking: `app/Models/Booking.php`
- Model Garage: `app/Models/GarageService.php`, `app/Models/ServiceBooking.php`
- Model Community: `app/Models/Community.php`, `app/Models/CommunityPost.php`
- Model Wallet: `app/Models/Wallet.php`, `app/Models/WalletTransaction.php`
- Modul: `Modules/*/Routes/api.php`
