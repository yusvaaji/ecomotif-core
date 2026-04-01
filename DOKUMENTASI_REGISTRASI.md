# Dokumentasi Registrasi - User, Dealer/Showroom, dan Mediator

## Overview

Sistem ini mendukung 3 jenis registrasi:
1. **Regular User** - Konsumen biasa
2. **Dealer/Showroom** - Penjual/dealer/showroom
3. **Mediator/Calo** - Mediator yang membantu konsumen

---

## 1. Registrasi Regular User (Konsumen)

### Endpoint
```
POST /api/store-register
```

### Request Body
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Field Validation
- `name` (required, string, max:255) - Nama lengkap user
- `email` (required, string, email, max:255, unique) - Email user (harus unique)
- `password` (required, string, min:4, max:100, confirmed) - Password user

### Response Success (200)
```json
{
  "message": "Account created successful, a verification OTP has been send to your mail, please verify it"
}
```

### Response Error (422 - Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email already exist"],
    "password": ["Password confirmation does not match"]
  }
}
```

### Flow Registrasi
1. User submit form registrasi
2. System create user dengan status:
   - `status`: 'enable'
   - `is_banned`: 'no'
   - `is_dealer`: 0 (default)
   - `is_mediator`: 0 (default)
   - `verification_otp`: 6 digit random number
3. System kirim email dengan OTP
4. User harus verify email dengan OTP

### Verifikasi Email
**Endpoint:** `POST /api/user-verification`

**Request Body:**
```json
{
  "email": "john@example.com",
  "otp": "123456"
}
```

**Response Success:**
```json
{
  "message": "Verification Successfully"
}
```

**Response Error:**
```json
{
  "message": "Invalid token or email"
}
```

### Resend OTP
**Endpoint:** `POST /api/resend-register`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

---

## 2. Registrasi Dealer/Showroom

### Endpoint
```
POST /api/seller/store-register
```

### Request Body
```json
{
  "name": "ABC Showroom",
  "email": "showroom@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "address": "Jl. Raya No. 123, Jakarta"
}
```

### Field Validation
- `name` (required, string, max:255) - Nama showroom/dealer
- `email` (required, string, email, max:255, unique) - Email dealer (harus unique)
- `password` (required, string, min:4, max:100, confirmed) - Password dealer
- `phone` (required, string) - Nomor telepon dealer
- `address` (required, string) - Alamat dealer/showroom

### Response Success (200)
```json
{
  "message": "Seller account created successful, a verification OTP has been send to your mail, please verify it"
}
```

### Response Error (422 - Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": ["Phone is required"],
    "address": ["Address is required"]
  }
}
```

### Flow Registrasi
1. Dealer submit form registrasi
2. System create user dengan status:
   - `status`: 'enable'
   - `is_banned`: 'no'
   - `is_dealer`: 1 (set sebagai dealer)
   - `is_mediator`: 0 (default)
   - `showroom_id`: null
   - `barcode`: null (akan di-generate setelah registrasi)
   - `verification_otp`: 6 digit random number
3. System kirim email dengan OTP
4. Dealer harus verify email dengan OTP
5. Setelah verifikasi, dealer bisa login dan generate barcode

### Generate Barcode (Setelah Login)
**Endpoint:** `POST /api/user/showroom/generate-barcode`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Barcode generated successfully",
  "barcode": "SHOWROOM-ABC123XYZ-1",
  "qr_code": "SHOWROOM-ABC123XYZ-1"
}
```

### Verifikasi Email
Sama seperti regular user, gunakan endpoint `POST /api/user-verification`

---

## 3. Registrasi Mediator/Calo

### Endpoint
```
POST /api/mediator/store-register
```

### Request Body
```json
{
  "name": "Mediator Name",
  "email": "mediator@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "address": "Jl. Raya No. 456, Jakarta",
  "showroom_id": 1
}
```

### Field Validation
- `name` (required, string, max:255) - Nama mediator
- `email` (required, string, email, max:255, unique) - Email mediator (harus unique)
- `password` (required, string, min:4, max:100, confirmed) - Password mediator
- `phone` (required, string) - Nomor telepon mediator
- `address` (required, string) - Alamat mediator
- `showroom_id` (optional, integer, exists:users,id) - ID showroom jika mediator terikat ke showroom tertentu

### Response Success (200)
```json
{
  "message": "Mediator account created successful, a verification OTP has been send to your mail, please verify it"
}
```

### Response Error (422 - Validation Error)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "showroom_id": ["Showroom not found"]
  }
}
```

### Flow Registrasi
1. Mediator submit form registrasi
2. System verify showroom_id (jika provided):
   - Check apakah showroom exists
   - Check apakah showroom adalah dealer (`is_dealer = 1`)
   - Check apakah showroom status aktif
3. System create user dengan status:
   - `status`: 'enable'
   - `is_banned`: 'no'
   - `is_dealer`: 0 (default)
   - `is_mediator`: 1 (set sebagai mediator)
   - `showroom_id`: ID showroom (jika provided) atau null
   - `verification_otp`: 6 digit random number
4. System kirim email dengan OTP
5. Mediator harus verify email dengan OTP
6. Setelah verifikasi, mediator bisa login dan mulai membuat aplikasi untuk konsumen

### Verifikasi Email
Sama seperti regular user, gunakan endpoint `POST /api/user-verification`

---

## 4. Login Setelah Registrasi

### Endpoint
```
POST /api/store-login
```

### Request Body
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

### Response Success (200)

**Untuk Regular User:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "username": "john-doe-20250114123456",
    "name": "John Doe",
    "image": null,
    "status": "enable",
    "is_banned": "no",
    "is_dealer": 0,
    "is_mediator": 0,
    "designation": null,
    "address": null,
    "phone": null,
    "kyc_status": null
  },
  "user_type": "user"
}
```

**Untuk Dealer/Showroom:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 2,
    "username": "abc-showroom-20250114123456",
    "name": "ABC Showroom",
    "image": null,
    "status": "enable",
    "is_banned": "no",
    "is_dealer": 1,
    "is_mediator": 0,
    "designation": null,
    "address": "Jl. Raya No. 123",
    "phone": "+1234567890",
    "kyc_status": null,
    "showroom_id": null
  },
  "user_type": "dealer"
}
```

**Untuk Mediator:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 3,
    "username": "mediator-name-20250114123456",
    "name": "Mediator Name",
    "image": null,
    "status": "enable",
    "is_banned": "no",
    "is_dealer": 0,
    "is_mediator": 1,
    "designation": null,
    "address": "Jl. Raya No. 456",
    "phone": "+1234567890",
    "kyc_status": null,
    "showroom_id": 2
  },
  "user_type": "mediator"
}
```

### Response Error (401 - Unauthorized)
```json
{
  "message": "Invalid credentials"
}
```

---

## 5. Contoh Request Menggunakan cURL

### Registrasi Regular User
```bash
curl -X POST http://your-domain.com/api/store-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Registrasi Dealer/Showroom
```bash
curl -X POST http://your-domain.com/api/seller/store-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "ABC Showroom",
    "email": "showroom@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "address": "Jl. Raya No. 123, Jakarta"
  }'
```

### Registrasi Mediator
```bash
curl -X POST http://your-domain.com/api/mediator/store-register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mediator Name",
    "email": "mediator@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "address": "Jl. Raya No. 456, Jakarta",
    "showroom_id": 1
  }'
```

### Verifikasi Email
```bash
curl -X POST http://your-domain.com/api/user-verification \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "otp": "123456"
  }'
```

### Login
```bash
curl -X POST http://your-domain.com/api/store-login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

---

## 6. Contoh Request Menggunakan JavaScript (Fetch API)

### Registrasi Regular User
```javascript
const registerUser = async () => {
  const response = await fetch('http://your-domain.com/api/store-register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123'
    })
  });
  
  const data = await response.json();
  console.log(data);
};
```

### Registrasi Dealer/Showroom
```javascript
const registerDealer = async () => {
  const response = await fetch('http://your-domain.com/api/seller/store-register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      name: 'ABC Showroom',
      email: 'showroom@example.com',
      password: 'password123',
      password_confirmation: 'password123',
      phone: '+1234567890',
      address: 'Jl. Raya No. 123, Jakarta'
    })
  });
  
  const data = await response.json();
  console.log(data);
};
```

### Registrasi Mediator
```javascript
const registerMediator = async () => {
  const response = await fetch('http://your-domain.com/api/mediator/store-register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      name: 'Mediator Name',
      email: 'mediator@example.com',
      password: 'password123',
      password_confirmation: 'password123',
      phone: '+1234567890',
      address: 'Jl. Raya No. 456, Jakarta',
      showroom_id: 1 // optional
    })
  });
  
  const data = await response.json();
  console.log(data);
};
```

### Login
```javascript
const login = async () => {
  const response = await fetch('http://your-domain.com/api/store-login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: 'john@example.com',
      password: 'password123'
    })
  });
  
  const data = await response.json();
  
  if (data.access_token) {
    // Save token to localStorage or state
    localStorage.setItem('token', data.access_token);
    localStorage.setItem('user_type', data.user_type);
    console.log('Login successful:', data);
  }
};
```

---

## 7. Flow Diagram

### Regular User Registration Flow
```
1. User Submit Form
   ↓
2. POST /api/store-register
   ↓
3. System Create User (is_dealer=0, is_mediator=0)
   ↓
4. System Send OTP Email
   ↓
5. User Verify Email (POST /api/user-verification)
   ↓
6. User Can Login (POST /api/store-login)
   ↓
7. Response: user_type = "user"
```

### Dealer/Showroom Registration Flow
```
1. Dealer Submit Form
   ↓
2. POST /api/seller/store-register
   ↓
3. System Create User (is_dealer=1, is_mediator=0)
   ↓
4. System Send OTP Email
   ↓
5. Dealer Verify Email (POST /api/user-verification)
   ↓
6. Dealer Login (POST /api/store-login)
   ↓
7. Response: user_type = "dealer"
   ↓
8. Dealer Generate Barcode (POST /api/user/showroom/generate-barcode)
```

### Mediator Registration Flow
```
1. Mediator Submit Form (with optional showroom_id)
   ↓
2. POST /api/mediator/store-register
   ↓
3. System Verify Showroom (if showroom_id provided)
   ↓
4. System Create User (is_dealer=0, is_mediator=1, showroom_id)
   ↓
5. System Send OTP Email
   ↓
6. Mediator Verify Email (POST /api/user-verification)
   ↓
7. Mediator Login (POST /api/store-login)
   ↓
8. Response: user_type = "mediator"
```

---

## 8. Perbedaan Role Setelah Registrasi

| Field | Regular User | Dealer/Showroom | Mediator |
|-------|--------------|----------------|----------|
| `is_dealer` | 0 | 1 | 0 |
| `is_mediator` | 0 | 0 | 1 |
| `showroom_id` | null | null | ID showroom (optional) |
| `barcode` | null | null (generate setelah login) | null |
| `user_type` (login response) | "user" | "dealer" | "mediator" |

---

## 9. Catatan Penting

1. **Email Verification Wajib**: Semua user harus verify email dengan OTP sebelum bisa login
2. **Unique Email**: Email harus unique untuk semua jenis user
3. **Password Minimum**: Password minimal 4 karakter
4. **Showroom ID untuk Mediator**: Optional, tapi jika diisi harus valid showroom (is_dealer=1)
5. **Barcode untuk Dealer**: Barcode di-generate setelah login, bukan saat registrasi
6. **KYC Status**: KYC status akan null saat registrasi, bisa di-update kemudian

---

## 10. Troubleshooting

### Error: "Email already exist"
**Solusi:** Email sudah terdaftar, gunakan email lain atau reset password

### Error: "Showroom not found"
**Solusi:** Pastikan showroom_id valid dan showroom adalah dealer aktif

### Error: "Invalid token or email"
**Solusi:** 
- Pastikan OTP yang diinput benar
- Pastikan email sudah terdaftar
- Request OTP baru dengan `/api/resend-register`

### Error: "Email already verified"
**Solusi:** Email sudah terverifikasi, langsung login saja

### Error: "Invalid credentials" saat login
**Solusi:**
- Pastikan email dan password benar
- Pastikan email sudah terverifikasi
- Pastikan user status = 'enable' dan is_banned = 'no'

---

## 11. Testing Checklist

- [ ] Registrasi regular user berhasil
- [ ] Email OTP terkirim
- [ ] Verifikasi email berhasil
- [ ] Login regular user berhasil, user_type = "user"
- [ ] Registrasi dealer berhasil
- [ ] Email OTP terkirim
- [ ] Verifikasi email berhasil
- [ ] Login dealer berhasil, user_type = "dealer"
- [ ] Generate barcode dealer berhasil
- [ ] Registrasi mediator berhasil (tanpa showroom_id)
- [ ] Registrasi mediator berhasil (dengan showroom_id valid)
- [ ] Registrasi mediator gagal (dengan showroom_id invalid)
- [ ] Email OTP terkirim
- [ ] Verifikasi email berhasil
- [ ] Login mediator berhasil, user_type = "mediator"
- [ ] Resend OTP berhasil
- [ ] Error handling untuk semua validasi

---

**Dokumentasi ini mencakup semua informasi yang diperlukan untuk registrasi user, dealer/showroom, dan mediator.**




