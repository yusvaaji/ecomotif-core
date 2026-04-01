# Panduan Registrasi - User, Dealer/Showroom, dan Mediator

## Ringkasan

Sistem mendukung 3 jenis registrasi:
1. **User Biasa** - Konsumen
2. **Dealer/Showroom** - Penjual
3. **Mediator/Calo** - Mediator

---

## 1. Registrasi User Biasa

### Endpoint
```
POST /api/store-register
```

### Data yang Diperlukan
```json
{
  "name": "Nama Lengkap",
  "email": "email@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Langkah-langkah
1. Kirim request ke endpoint
2. Sistem akan kirim OTP ke email
3. Verifikasi email dengan OTP: `POST /api/user-verification`
4. Login: `POST /api/store-login`

---

## 2. Registrasi Dealer/Showroom

### Endpoint
```
POST /api/seller/store-register
```

### Data yang Diperlukan
```json
{
  "name": "Nama Showroom",
  "email": "showroom@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+6281234567890",
  "address": "Alamat Lengkap"
}
```

### Langkah-langkah
1. Kirim request ke endpoint
2. Sistem akan kirim OTP ke email
3. Verifikasi email dengan OTP: `POST /api/user-verification`
4. Login: `POST /api/store-login` (user_type = "dealer")
5. Generate barcode: `POST /api/user/showroom/generate-barcode`

---

## 3. Registrasi Mediator

### Endpoint
```
POST /api/mediator/store-register
```

### Data yang Diperlukan
```json
{
  "name": "Nama Mediator",
  "email": "mediator@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+6281234567890",
  "address": "Alamat Lengkap",
  "showroom_id": 1
}
```

**Catatan:** `showroom_id` adalah optional. Jika diisi, harus ID showroom yang valid.

### Langkah-langkah
1. Kirim request ke endpoint
2. Sistem akan verify showroom (jika showroom_id diisi)
3. Sistem akan kirim OTP ke email
4. Verifikasi email dengan OTP: `POST /api/user-verification`
5. Login: `POST /api/store-login` (user_type = "mediator")

---

## 4. Verifikasi Email

### Endpoint
```
POST /api/user-verification
```

### Data yang Diperlukan
```json
{
  "email": "email@example.com",
  "otp": "123456"
}
```

### Resend OTP
Jika OTP tidak sampai, gunakan:
```
POST /api/resend-register
{
  "email": "email@example.com"
}
```

---

## 5. Login

### Endpoint
```
POST /api/store-login
```

### Data yang Diperlukan
```json
{
  "email": "email@example.com",
  "password": "password123"
}
```

### Response
Response akan berbeda berdasarkan role:

**User Biasa:**
```json
{
  "access_token": "...",
  "user_type": "user",
  "user": { "is_dealer": 0, "is_mediator": 0 }
}
```

**Dealer:**
```json
{
  "access_token": "...",
  "user_type": "dealer",
  "user": { "is_dealer": 1, "is_mediator": 0 }
}
```

**Mediator:**
```json
{
  "access_token": "...",
  "user_type": "mediator",
  "user": { "is_dealer": 0, "is_mediator": 1, "showroom_id": 1 }
}
```

---

## 6. Contoh Menggunakan Postman

### Registrasi User
1. Method: POST
2. URL: `http://your-domain.com/api/store-register`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Registrasi Dealer
1. Method: POST
2. URL: `http://your-domain.com/api/seller/store-register`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "name": "ABC Showroom",
  "email": "showroom@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+6281234567890",
  "address": "Jl. Raya No. 123, Jakarta"
}
```

### Registrasi Mediator
1. Method: POST
2. URL: `http://your-domain.com/api/mediator/store-register`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "name": "Mediator Name",
  "email": "mediator@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+6281234567890",
  "address": "Jl. Raya No. 456, Jakarta",
  "showroom_id": 1
}
```

---

## 7. Perbedaan Role

| Field | User | Dealer | Mediator |
|-------|------|--------|----------|
| `is_dealer` | 0 | 1 | 0 |
| `is_mediator` | 0 | 0 | 1 |
| `showroom_id` | null | null | ID showroom (optional) |
| `barcode` | null | Generate setelah login | null |
| `user_type` | "user" | "dealer" | "mediator" |

---

## 8. Tips

1. **Email Harus Unique**: Satu email hanya bisa untuk satu akun
2. **Password Minimal 4 Karakter**: Tidak perlu terlalu kompleks
3. **Verifikasi Email Wajib**: Harus verify email sebelum bisa login
4. **Showroom ID untuk Mediator**: Optional, tapi jika diisi harus valid
5. **Barcode untuk Dealer**: Generate setelah login, bukan saat registrasi

---

## 9. Troubleshooting

### Email sudah terdaftar
**Solusi:** Gunakan email lain atau reset password

### OTP tidak sampai
**Solusi:** 
1. Check spam folder
2. Request OTP baru: `POST /api/resend-register`
3. Pastikan email benar

### Showroom tidak ditemukan (untuk Mediator)
**Solusi:** 
1. Pastikan showroom_id valid
2. Pastikan showroom adalah dealer aktif
3. Atau kosongkan showroom_id (optional)

### Login gagal setelah verifikasi
**Solusi:**
1. Pastikan email dan password benar
2. Pastikan email sudah terverifikasi
3. Pastikan user status = 'enable'

---

## 10. Flow Diagram Sederhana

```
REGISTRASI
    ↓
KIRIM REQUEST
    ↓
TERIMA OTP DI EMAIL
    ↓
VERIFIKASI EMAIL
    ↓
LOGIN
    ↓
GUNAKAN SISTEM
```

**Untuk Dealer:**
```
LOGIN
    ↓
GENERATE BARCODE
    ↓
GUNAKAN BARCODE UNTUK SHOWROOM
```

---

**Dokumentasi lengkap ada di: `DOKUMENTASI_REGISTRASI.md`**




