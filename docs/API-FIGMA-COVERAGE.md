# Mobile Figma Coverage and Web Parity

Dokumen ini menjadi acuan implementasi fitur Mobile (berdasarkan Figma) dan kewajiban parity ke Web.
Prinsip utama: **fitur yang ada di mobile harus punya versi web** (minimal read-only + aksi inti).

Referensi endpoint rinci ada di `docs/API.md`.

---

## 1) Login and Account Access

| Kebutuhan Mobile (Figma) | API | Web | Status |
|---|---|---|---|
| Login email/password | `POST /api/store-login` | `GET /login`, `POST /store-login` | Covered |
| Forgot password | `POST /api/send-forget-password` | `GET /forgot-password`, `POST /forgot-password` | Covered |
| Verify OTP reset | `POST /api/verify-forget-password-otp` | `GET /reset-password/{token}` | Covered |
| Save new password | `POST /api/store-reset-password` | `POST /reset-password` | Covered |
| Logout | `GET /api/user-logout` | `POST /logout` | Covered |

---

## 2) Tenant / Company Access Model

Di sistem saat ini, konsep tenant/company direpresentasikan sebagai role akun:

| Company/Tenant Type | Field User | API Access Scope | Web Access Scope |
|---|---|---|---|
| Showroom company | `is_dealer = 1` | `/api/user/showroom/*`, katalog `car` | Dashboard user + orders showroom |
| Garage company | `is_garage = 1` | `/api/user/garage/*` | `/user/garage/dashboard` + manajemen service/booking |
| Mediator company user | `is_mediator = 1` | `/api/user/mediator/*` | Belum ada dashboard web dedicated |
| Marketing company user | linked `showroom_id` + rule marketing | `/api/user/marketing/*` | Belum ada dashboard web dedicated |
| Consumer | default user | `/api/user/*` umum | Halaman user profile/orders/wallet/community |

Catatan:
- Registrasi company sudah dipisah endpoint: showroom, garage, mediator.
- Belum ada tabel tenant terpisah (multi-tenant isolation by company_id). Scope masih berbasis role user.

---

## 3) Mobile Feature Matrix and Web Parity

Legenda:
- **Covered**: mobile + web tersedia.
- **Partial**: ada, tapi belum setara penuh dengan Figma.
- **Gap**: mobile ada di Figma, versi web belum ada/dedicated belum ada.

### 3.1 Discovery and Catalog

| Fitur Mobile | API | Web | Parity |
|---|---|---|---|
| Home discovery | `GET /api/` | `/` | Covered |
| Listings + filter | `GET /api/listings` | `/listings` | Covered |
| Listing detail | `GET /api/listing/{id}` | `/listing/{slug}` | Covered |
| Dealer/showroom list | `GET /api/showrooms`, `GET /api/dealers` | `/dealers` | Covered |
| Dealer detail | `GET /api/dealer/{slug}` | `/dealer/{slug}` | Covered |
| Garage list/detail | `GET /api/garages`, `GET /api/garages/{id}` | `/garages`, `/garages/{id}` | Covered |
| Community list/detail | `GET /api/communities`, `GET /api/communities/{slug}` | `/communities`, `/communities/{slug}` | Covered |

### 3.2 Customer Ordering (Showroom + Garage)

| Fitur Mobile | API | Web | Parity |
|---|---|---|---|
| Submit leasing application | `POST /api/user/applications` | via alur user order | Covered |
| Upload application documents | `POST /api/user/applications/{id}/documents` | tersedia di flow order | Covered |
| Application status/detail | `GET /api/user/applications`, `GET /api/user/applications/{id}` | `/user/orders?tab=showroom` | Covered |
| Pay DP | `POST /api/user/applications/{id}/pay-dp` | alur belum final | Partial (stub backend) |
| Service booking create | `POST /api/user/service-bookings` | `POST /user/service-bookings` | Covered |
| Service booking history/detail/cancel | `GET/POST` booking routes | `/user/service-bookings*` | Covered |
| Unified orders tabs (showroom+garage) | API terpisah + aggregator UI | `/user/orders?tab=showroom|bengkel` | Covered |

### 3.3 Profile, Wallet, Notifications

| Fitur Mobile | API | Web | Parity |
|---|---|---|---|
| View/edit profile | `GET /api/user/edit-profile`, `PUT /api/user/update-profile` | `/user/edit-profile` | Covered |
| Change password | `POST /api/user/update-password` | `/user/change-password` | Covered |
| Wishlist + review | `wishlists`, `add/remove`, `store-review` | `/user/wishlists`, `/user/reviews` | Covered |
| Wallet + transactions | `/api/user/wallet*` | `/user/wallet` | Covered |
| Notifications + mark read | `/api/user/notifications*` | `/user/notifications` | Covered |
| Security prefs (biometric/auto-logout) | belum backend dedicated | belum dedicated | Partial (client-side) |

### 3.4 Partner Features (Company)

| Fitur Mobile Company | API | Web | Parity |
|---|---|---|---|
| Showroom dashboard app list | `/api/user/showroom/applications*` | `/user/orders?tab=showroom` | Covered |
| Showroom workflow (review/pool/appeal/result/dp) | endpoint lengkap `/api/user/showroom/*` | belum semua aksi dedicated page | Partial |
| Showroom barcode generation | `/api/user/showroom/generate-barcode` | belum halaman web khusus barcode | Gap |
| Garage owner dashboard | `/api/user/garage/dashboard` | `/user/garage/dashboard` | Covered |
| Garage services CRUD + booking status | `/api/user/garage/services*`, `/bookings/*` | route web owner garage tersedia | Covered |
| Mediator dashboard and applications | `/api/user/mediator/*` | belum dashboard web dedicated | Gap |
| Marketing dashboard and applications | `/api/user/marketing/*` | belum dashboard web dedicated | Gap |

### 3.5 Community

| Fitur Mobile | API | Web | Parity |
|---|---|---|---|
| My communities | `GET /api/user/my-communities` | `/user/communities` | Covered |
| Create community | `POST /api/user/communities` | `/user/communities/create` + submit | Covered |
| Join/leave | `POST /api/user/communities/{slug}/join|leave` | route web sama | Covered |
| Post and comment | endpoints post/comment tersedia | route web sama | Covered |

---

## 4) Gap List to Enforce Mobile = Web

Fitur mobile yang masih butuh versi web/dedicated parity:

1. **Mediator Web Dashboard**
   - Tambahkan halaman web untuk list/detail/update aplikasi mediator.
2. **Marketing Web Dashboard**
   - Tambahkan halaman web untuk list/create aplikasi marketing.
3. **Showroom Advanced Actions on Web**
   - UI web untuk `pool-to-leasing`, `appeal`, `handle-dp`, `leasing-result`.
4. **Showroom Barcode Web Page**
   - Halaman untuk generate/lihat barcode showroom.
5. **Payment DP Finalization**
   - Endpoint `pay-dp` saat ini stub; perlu gateway final agar parity flow selesai.

---

## 5) Statement of Compliance

- Untuk flow customer utama (login, katalog, order showroom, booking bengkel, profile, wallet, notifikasi, komunitas), parity mobile-web **sudah tercapai**.
- Untuk flow company/tenant tertentu (mediator, marketing, sebagian showroom advanced), parity web masih **partial/gap** dan tercatat di daftar prioritas di atas.

---

## 6) References

- API detail: `docs/API.md`
- OpenAPI: `docs/openapi.yaml`
- API routes: `routes/api.php`, `Modules/*/Routes/api.php`
- Web routes: `routes/web.php`
