# Dokumentasi Admin User Management

## Overview

Fitur lengkap untuk mengelola users (Regular User, Dealer/Showroom, Mediator) dari admin panel, termasuk:
- ✅ Form registrasi user baru dengan checklist
- ✅ List users dengan filter
- ✅ Reset password
- ✅ Update user information
- ✅ Delete user

---

## 1. Akses Menu

### URL Menu:
- **List Users:** `/admin/user-list`
- **Pending Users:** `/admin/pending-user`
- **Create User:** `/admin/user-create`
- **User Details:** `/admin/user-show/{id}`

### Di Sidebar Admin:
```
Users
├── User List
├── Pending Users
└── Create User
```

---

## 2. Create User (Registrasi dari Admin)

### URL
```
GET /admin/user-create
POST /admin/user-store
```

### Fitur:
- ✅ Form registrasi lengkap dengan checklist real-time
- ✅ Support 3 jenis user: Regular User, Dealer/Showroom, Mediator
- ✅ Validasi semua field
- ✅ Auto-verify email (tidak perlu OTP)
- ✅ Checklist untuk memastikan semua data terisi

### Form Fields:

#### Required Fields:
- **Name** - Nama lengkap user
- **Email** - Email user (harus unique)
- **Password** - Password (min 4 karakter)
- **Password Confirmation** - Konfirmasi password
- **User Type** - Pilih: User, Dealer, atau Mediator

#### Optional Fields:
- **Phone** - Nomor telepon
- **Address** - Alamat
- **Country** - Negara
- **Designation** - Jabatan/posisi
- **Showroom** - Showroom (hanya untuk Mediator)
- **Status** - Active/Inactive (default: Active)
- **Banned Status** - Banned/Not Banned (default: Not Banned)

### Checklist Real-time:
Form akan menampilkan checklist real-time untuk:
- ✓ Name: Status (Filled/Not filled)
- ✓ Email: Status (Valid email/Invalid/Not filled)
- ✓ Password: Status (Valid/Too short/Not filled)
- ✓ Password Confirmation: Status (Match/Not match/Not filled)
- ✓ User Type: Status (Selected)
- ✓ Phone: Status (Filled/Optional)
- ✓ Address: Status (Filled/Optional)
- ✓ Country: Status (Filled/Optional)
- ✓ Designation: Status (Filled/Optional)
- ✓ Showroom: Status (Selected/Optional) - hanya muncul untuk Mediator
- ✓ Status: Status (Set)
- ✓ Banned Status: Status (Set)

### Contoh Request:
```php
POST /admin/user-store
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "user_type": "dealer",
    "phone": "+1234567890",
    "address": "Jl. Raya No. 123",
    "country": "Indonesia",
    "designation": "Dealer",
    "status": "enable",
    "is_banned": "no"
}
```

### Response:
- Success: Redirect ke user list dengan notification success
- Error: Redirect back dengan error message

---

## 3. User List dengan Filter

### URL
```
GET /admin/user-list
```

### Fitur:
- ✅ List semua users dengan pagination
- ✅ Filter by User Type (User, Dealer, Mediator)
- ✅ Filter by Status (Active, Inactive)
- ✅ Search by name, email, phone
- ✅ Toggle status (enable/disable)
- ✅ View user details
- ✅ Delete user

### Filter Options:
1. **User Type:**
   - All User Types
   - Regular User
   - Dealer/Showroom
   - Mediator

2. **Status:**
   - Active
   - Inactive

3. **Search:**
   - Search by name, email, or phone

### Table Columns:
- Serial Number
- Name
- Email
- Role (badge: User/Dealer/Mediator)
- Phone
- Status (toggle switch)
- Action (View, Delete)

### Action Buttons:
- **View** - Lihat detail user
- **Delete** - Hapus user (dengan konfirmasi)

---

## 4. User Details (Show)

### URL
```
GET /admin/user-show/{id}
```

### Fitur:
- ✅ Informasi lengkap user
- ✅ Statistics (Active Car, Total Car, Total Purchase, Total Review)
- ✅ List semua cars yang dimiliki user
- ✅ Edit user information
- ✅ Reset password
- ✅ Delete user

### Statistics Cards:
- **Active Car** - Jumlah mobil aktif
- **Total Car** - Total mobil yang dimiliki
- **Total Purchase** - Total pembelian subscription
- **Total Review** - Total review yang diterima

### Action Buttons:
- **Edit Profile** - Edit informasi user
- **Reset Password** - Reset password user
- **Delete** - Hapus user

---

## 5. Reset Password

### URL
```
POST /admin/user-reset-password/{id}
```

### Fitur:
- ✅ Reset password user dari admin
- ✅ Validasi password (min 4 karakter)
- ✅ Konfirmasi password harus match
- ✅ Password langsung berubah (user harus login dengan password baru)

### Form Fields:
- **New Password** (required, min 4 characters)
- **Confirm Password** (required, must match)

### Warning:
Sistem akan menampilkan warning bahwa password akan langsung berubah dan user harus menggunakan password baru untuk login.

### Contoh Request:
```php
POST /admin/user-reset-password/1
{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

### Response:
- Success: Redirect back dengan notification success
- Error: Redirect back dengan error message

---

## 6. Update User Information

### URL
```
PUT /admin/user-update/{id}
```

### Fitur:
- ✅ Update informasi user
- ✅ Update status (enable/disable)
- ✅ Update banned status

### Form Fields:
- Name
- Phone
- Address
- Designation
- About Me
- Status (checkbox)

---

## 7. Delete User

### URL
```
DELETE /admin/user-delete/{id}
```

### Fitur:
- ✅ Delete user dengan konfirmasi
- ✅ Validasi: Tidak bisa delete jika user punya cars
- ✅ Auto delete related data:
  - Reviews
  - Subscription History
  - Wishlists
  - KYC Information
  - User Image

### Validasi:
User tidak bisa dihapus jika:
- Memiliki cars yang terdaftar

---

## 8. Checklist Data Users

### Field di Table Users:

#### Required Fields (Auto-filled atau dari form):
- ✅ `name` - Nama user
- ✅ `email` - Email (unique)
- ✅ `username` - Auto-generated dari name
- ✅ `password` - Password (hashed)
- ✅ `status` - enable/disable (default: enable)
- ✅ `is_banned` - yes/no (default: no)
- ✅ `is_dealer` - 0/1 (berdasarkan user_type)
- ✅ `is_mediator` - 0/1 (berdasarkan user_type)
- ✅ `email_verified_at` - Auto-set saat create dari admin

#### Optional Fields:
- ⚪ `phone` - Nomor telepon
- ⚪ `address` - Alamat
- ⚪ `country` - Negara
- ⚪ `designation` - Jabatan
- ⚪ `showroom_id` - ID showroom (untuk mediator)
- ⚪ `barcode` - Barcode (untuk dealer, generate setelah login)
- ⚪ `is_influencer` - 0/1 (default: 0)
- ⚪ `image` - Foto profil
- ⚪ `about_me` - Tentang user
- ⚪ `kyc_status` - Status KYC

#### System Fields (Auto-managed):
- `verification_token` - Token verifikasi
- `verification_otp` - OTP verifikasi
- `provider` - Social login provider
- `provider_id` - Social login ID
- `remember_token` - Remember me token
- `created_at` - Waktu dibuat
- `updated_at` - Waktu diupdate

---

## 9. User Type Configuration

### Regular User:
```php
is_dealer = 0
is_mediator = 0
showroom_id = null
```

### Dealer/Showroom:
```php
is_dealer = 1
is_mediator = 0
showroom_id = null
barcode = null (generate setelah login)
```

### Mediator:
```php
is_dealer = 0
is_mediator = 1
showroom_id = ID showroom (optional)
```

---

## 10. Routes Summary

| Method | URL | Controller Method | Description |
|--------|-----|-------------------|-------------|
| GET | `/admin/user-list` | `user_list()` | List users dengan filter |
| GET | `/admin/pending-user` | `pending_user()` | List pending users |
| GET | `/admin/user-create` | `create()` | Form create user |
| POST | `/admin/user-store` | `store()` | Store new user |
| GET | `/admin/user-show/{id}` | `user_show()` | User details |
| POST | `/admin/user-reset-password/{id}` | `reset_password()` | Reset password |
| PUT | `/admin/user-update/{id}` | `update()` | Update user |
| PUT | `/admin/user-status/{id}` | `user_status()` | Toggle status |
| DELETE | `/admin/user-delete/{id}` | `user_destroy()` | Delete user |

---

## 11. Validasi

### Create User:
- Name: required, string, max:255
- Email: required, email, unique:users
- Password: required, min:4, max:100, confirmed
- Phone: nullable, string
- Address: nullable, string, max:500
- Country: nullable, string
- Designation: nullable, string, max:255
- User Type: required, in:user,dealer,mediator
- Showroom ID: nullable, integer, exists:users,id (jika mediator)
- Status: nullable, in:enable,disable
- Banned: nullable, in:yes,no

### Reset Password:
- Password: required, min:4, max:100, confirmed

### Update User:
- Name: required
- Phone: required
- Address: required, max:220

---

## 12. Tips & Best Practices

1. **Saat Create User:**
   - Pastikan email unique
   - Password minimal 4 karakter
   - Untuk Mediator, pilih showroom jika ada
   - Email akan auto-verified (tidak perlu OTP)

2. **Saat Reset Password:**
   - Pastikan password dan confirmation match
   - Informasikan user bahwa password sudah direset
   - User harus login dengan password baru

3. **Saat Delete User:**
   - Pastikan user tidak punya cars
   - Related data akan otomatis terhapus
   - User image akan terhapus dari storage

4. **Filter Users:**
   - Gunakan filter untuk mencari users spesifik
   - Search bisa digunakan untuk name, email, atau phone
   - Filter bisa dikombinasikan

---

## 13. Screenshot/Flow

### Create User Flow:
```
1. Klik "Create User" di user list
   ↓
2. Isi form dengan checklist real-time
   ↓
3. Pilih user type (User/Dealer/Mediator)
   ↓
4. Jika Mediator, pilih showroom (optional)
   ↓
5. Set status dan banned status
   ↓
6. Submit form
   ↓
7. Redirect ke user list dengan notification
```

### Reset Password Flow:
```
1. Buka user details
   ↓
2. Klik "Reset Password" button
   ↓
3. Isi new password dan confirmation
   ↓
4. Submit form
   ↓
5. Password langsung berubah
   ↓
6. User harus login dengan password baru
```

---

**Dokumentasi ini mencakup semua fitur admin user management yang sudah diimplementasikan.**




