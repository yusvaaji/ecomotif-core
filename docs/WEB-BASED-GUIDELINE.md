# Web-Based Guideline: Login and Feature Access

Dokumen ini menjadi panduan operasional untuk penggunaan aplikasi web Ecomotif.
Fokus dokumen:
- alur login,
- akses berdasarkan tipe user (customer/company),
- daftar fitur web yang tersedia,
- checklist parity dengan mobile.

---

## 1. Tujuan

Guideline ini dipakai oleh:
- tim produk (validasi flow),
- tim QA (test scenario),
- tim support/ops (troubleshooting akses),
- tim dev (acuan implementasi parity mobile-web).

---

## 2. Terminologi User (Web)

| Tipe User | Penanda | Keterangan |
|---|---|---|
| Customer | user default | User pembeli/penyewa/booking service |
| Showroom Partner | `is_dealer = 1` | Mitra showroom/dealer |
| Garage Partner | `is_garage = 1` | Mitra bengkel |
| Mediator | `is_mediator = 1` | Perantara aplikasi leasing |
| Marketing | user marketing + `showroom_id` | User marketing terkait showroom |

Catatan:
- Akses fitur ditentukan oleh kombinasi login + middleware role.
- Jika role tidak sesuai, endpoint akan ditolak (403).
- **Registrasi mitra (API mobile / parity):** `POST /api/seller/store-register` dan `POST /api/garage/store-register` menyimpan data onboarding di tabel **`merchant_profiles`** (satu baris per user, `business_type` = `showroom` atau `garage`), termasuk **`subscription_plan_id`** (paket aktif dari `subscription_plans`). Identitas akun tetap di `users`. Rinci field dan respons profil: `docs/API.md` §2 dan §15, `docs/openapi.yaml`.

---

## 3. Guideline Login Web

### 3.1 Alur standar

1. User buka halaman login web: `GET /login`
2. User kirim kredensial: `POST /store-login`
3. Jika sukses:
   - session login aktif,
   - diarahkan ke dashboard/halaman user sesuai role.
4. Jika gagal:
   - tampilkan pesan validasi (email/password salah, akun belum aktif, dsb).

### 3.2 Alur lupa password

1. Buka: `GET /forgot-password`
2. Submit email: `POST /forgot-password`
3. Terima token/link reset
4. Buka reset page: `GET /reset-password/{token}`
5. Simpan password baru: `POST /reset-password`

### 3.3 Logout

- Logout web: `POST /logout`

### 3.4 Social Login (jika diaktifkan)

- Google: `GET /login/google`
- Facebook: `GET /login/facebook`

---

## 4. Access Matrix (Web Routes)

| Area | Route Web | Login Required | Role |
|---|---|---|---|
| Public home | `/` | No | Semua |
| Listings | `/listings`, `/listing/{slug}` | No | Semua |
| Dealers | `/dealers`, `/dealer/{slug}` | No | Semua |
| Garages | `/garages`, `/garages/{id}` | No/Yes (booking yes) | Semua |
| Communities public | `/communities`, `/communities/{slug}` | No | Semua |
| User profile | `/user/edit-profile` | Yes | Semua user login |
| User orders | `/user/orders` | Yes | Semua user login |
| Wallet | `/user/wallet` | Yes | Semua user login |
| Notifications | `/user/notifications` | Yes | Semua user login |
| User communities | `/user/communities*` | Yes | Semua user login |
| Garage dashboard | `/user/garage/dashboard` | Yes | Garage partner |

---

## 5. Feature Guideline per Role (Web)

### 5.1 Customer

#### Fitur inti
- Browse katalog unit: `/listings`
- Lihat detail unit: `/listing/{slug}`
- Lihat dealer/showroom: `/dealers`, `/dealer/{slug}`
- Booking service bengkel:
  - list/detail: `/garages`, `/garages/{id}`
  - riwayat booking: `/user/service-bookings`
- Order history:
  - showroom tab: `/user/orders?tab=showroom`
  - bengkel tab: `/user/orders?tab=bengkel`
- Profile:
  - edit profile: `/user/edit-profile`
  - change password: `/user/change-password`
  - wishlist/review: `/user/wishlists`, `/user/reviews`
- Wallet and notifications:
  - `/user/wallet`
  - `/user/notifications`

#### Ekspektasi minimum QA
- User bisa login/logout normal.
- User bisa create booking bengkel dan membatalkan jika status memungkinkan.
- User bisa melihat order showroom dan bengkel di halaman orders.
- User bisa mark read notifikasi.

### 5.2 Showroom Partner

#### Fitur inti web yang sudah ada
- Manage listing kendaraan (create/update/delete via flow user dealer)
- Lihat order/pengajuan di `/user/orders?tab=showroom`

#### Catatan parity
- Aksi API lanjutan showroom (pool-to-leasing, appeal, handle-dp, barcode) belum semuanya punya halaman web dedicated.

### 5.3 Garage Partner

#### Fitur inti
- Dashboard: `/user/garage/dashboard`
- Kelola layanan:
  - tambah layanan,
  - aktif/nonaktif layanan,
  - hapus layanan.
- Update status booking service dari dashboard garage.

#### Ekspektasi minimum QA
- Hanya user garage yang bisa akses dashboard garage.
- Transisi status booking mengikuti rule backend.

### 5.4 Mediator

#### Status
- API mediator sudah tersedia.
- Web dedicated mediator dashboard belum final.

### 5.5 Marketing

#### Status
- API marketing sudah tersedia.
- Web dedicated marketing dashboard belum final.

---

## 6. Checklist Mobile-Web Parity

Gunakan checklist ini setiap ada update fitur mobile:

- [ ] Login, forgot password, reset password tersedia di web.
- [ ] Fitur yang ditambahkan di mobile juga punya route + view web.
- [ ] Flow order customer (showroom + bengkel) tampil di `/user/orders`.
- [ ] Notifikasi dan wallet tetap bisa diakses dari web.
- [ ] Role-based access (garage/showroom/mediator/marketing) tervalidasi.
- [ ] Error handling konsisten (unauthorized, forbidden, validation).

---

## 7. Troubleshooting Akses

### 7.1 User login tapi fitur tidak muncul
- Cek role flag user (`is_dealer`, `is_garage`, `is_mediator`, `showroom_id`).
- Cek middleware route web.
- Cek status akun (active/banned/email verified).

### 7.2 Aksi ditolak 403
- Biasanya karena role mismatch atau ownership data bukan milik user login.
- Verifikasi user yang login sesuai entitas yang diakses.

### 7.3 Data terlihat di API tapi tidak di web
- Cek apakah route + blade view untuk fitur itu sudah dibuat.
- Cek controller web menggunakan source data yang sama dengan API.

---

## 8. Referensi Teknis

- Web route: `routes/web.php`
- API route: `routes/api.php`
- API detail: `docs/API.md`
- Mobile parity matrix: `docs/API-FIGMA-COVERAGE.md`
- OpenAPI: `docs/openapi.yaml`
