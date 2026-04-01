# Quick Reference - API Endpoints untuk Mobile App

## Base URL
```
/api
```

## Authentication
Semua protected endpoints memerlukan header:
```
Authorization: Bearer {access_token}
```

---

## 🔓 PUBLIC ENDPOINTS (Tidak Perlu Auth)

### Setup & Configuration
```
GET  /api/website-setup              - Setup awal (language, currency, settings)
GET  /api/language-switcher          - Switch language
GET  /api/currency-switcher          - Switch currency
GET  /api/cities-by-country/{id}     - List cities by country
```

### Home & Listings
```
GET  /api/                           - Home page data
GET  /api/all-brands                 - List semua brands
GET  /api/listings-filter-option     - Filter options untuk listings
GET  /api/listings                   - List mobil dengan filter
GET  /api/listing/{id}               - Detail mobil
```

### Dealers
```
GET  /api/dealers                    - List dealers
GET  /api/dealers-filter-option      - Filter options untuk dealers
GET  /api/dealer/{slug}              - Detail dealer
POST /api/send-message-to-dealer/{id} - Kirim pesan ke dealer
```

### Pages
```
GET  /api/terms-conditions            - Terms & conditions
GET  /api/privacy-policy             - Privacy policy
GET  /api/join-as-dealer            - Redirect ke KYC
```

### Authentication (Public)
```
POST /api/store-login                - Login
POST /api/store-register             - Register user biasa
POST /api/seller/store-register      - Register seller (⚠️ BELUM ADA IMPLEMENTASI)
POST /api/resend-register            - Resend OTP
POST /api/user-verification          - Verify email dengan OTP
POST /api/send-forget-password       - Request reset password
POST /api/verify-forget-password-otp - Verify OTP reset password
POST /api/store-reset-password       - Reset password
GET  /api/user-logout                - Logout (perlu auth)
```

---

## 🔒 PROTECTED ENDPOINTS (Perlu Auth: `auth:api`)

### Dashboard & Profile
```
GET    /api/user/dashboard                    - Dashboard user/dealer
GET    /api/user/edit-profile                 - Get profile data
PUT    /api/user/update-profile               - Update profile
GET    /api/user/change-password              - Get change password form
POST   /api/user/update-password              - Update password
POST   /api/user/upload-user-avatar           - Upload avatar
```

### Wishlist
```
GET    /api/user/wishlists                    - List wishlist
GET    /api/user/add-to-wishlist/{id}         - Add to wishlist
DELETE /api/user/remove-wishlist/{id}         - Remove from wishlist
```

### Reviews
```
GET    /api/user/reviews                      - List reviews user
POST   /api/user/store-review                - Create review
```

### Transactions
```
GET    /api/user/transactions                 - Subscription history
```

### Payment
```
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

---

## 🏪 DEALER-SPECIFIC ENDPOINTS (Perlu `is_dealer = 1`)

### Booking Management
```
GET    /api/user/booking-request                - List booking requests
GET    /api/user/booking-request-details/{id}   - Detail booking request
POST   /api/user/booking-request-accept/{id}    - Accept booking
POST   /api/user/booking-cancel-by-dealer/{id}  - Cancel booking (dealer)
POST   /api/user/booking-complete-by-dealer/{id} - Complete booking
```

### Withdraw
```
GET    /api/user/withdraw                        - Withdraw list
POST   /api/user/withdraw-request                - Request withdraw
```

---

## 📋 Response Format

### Success Response
```json
{
  "message": "Success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "message": "Error message"
}
```

**Status Codes:**
- `200` - Success
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (validation error, insufficient permissions)

---

## 🔑 Login Response Example

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 43200,
  "user": {
    "id": 1,
    "username": "john-doe",
    "name": "John Doe",
    "image": "uploads/custom-images/user-1.jpg",
    "status": "enable",
    "is_banned": "no",
    "is_dealer": 0,
    "designation": null,
    "address": "123 Main St",
    "phone": "+1234567890",
    "kyc_status": "disable"
  },
  "user_type": "user"
}
```

**Note:** `user_type` bisa `"user"` atau `"dealer"`

---

## 📝 Booking Status Codes

- `0` - Pending
- `1` - Approved
- `2` - Completed
- `3` - Cancelled by User
- `4` - Cancelled by Dealer
- `6` - Ride Started

---

## ⚠️ Issues yang Perlu Diperhatikan

1. **`/api/seller/store-register`** - Route ada tapi method `seller_store_register` tidak ada di `RegisterController`
2. **Booking Model** - Digunakan tapi file model tidak ditemukan
3. **Dealer-specific endpoints** - Beberapa mungkin perlu dicek apakah sudah terimplementasi di routes

---

**Untuk dokumentasi lengkap, lihat:** `DOKUMENTASI_ROLES_DAN_INTEGRASI.md`




