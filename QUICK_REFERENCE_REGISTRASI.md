# Quick Reference - Registrasi API

## Endpoints

| Role | Endpoint | Method |
|------|----------|--------|
| User | `/api/store-register` | POST |
| Dealer | `/api/seller/store-register` | POST |
| Mediator | `/api/mediator/store-register` | POST |
| Verifikasi | `/api/user-verification` | POST |
| Resend OTP | `/api/resend-register` | POST |
| Login | `/api/store-login` | POST |

---

## Request Body

### User
```json
{
  "name": "string",
  "email": "string (unique)",
  "password": "string (min:4)",
  "password_confirmation": "string"
}
```

### Dealer
```json
{
  "name": "string",
  "email": "string (unique)",
  "password": "string (min:4)",
  "password_confirmation": "string",
  "phone": "string (required)",
  "address": "string (required)"
}
```

### Mediator
```json
{
  "name": "string",
  "email": "string (unique)",
  "password": "string (min:4)",
  "password_confirmation": "string",
  "phone": "string (required)",
  "address": "string (required)",
  "showroom_id": "integer (optional)"
}
```

### Verifikasi
```json
{
  "email": "string",
  "otp": "string (6 digits)"
}
```

### Login
```json
{
  "email": "string",
  "password": "string"
}
```

---

## Response Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 401 | Unauthorized (invalid credentials) |
| 403 | Forbidden (email already verified, invalid showroom) |
| 422 | Validation Error |

---

## User Type After Login

| Role | `user_type` | `is_dealer` | `is_mediator` |
|------|-------------|-------------|---------------|
| User | "user" | 0 | 0 |
| Dealer | "dealer" | 1 | 0 |
| Mediator | "mediator" | 0 | 1 |

---

## Flow

1. **Register** → 2. **Verify Email** → 3. **Login** → 4. **Use System**

**Dealer tambahan:** 4. **Generate Barcode**

---

## cURL Examples

### Register User
```bash
curl -X POST http://your-domain.com/api/store-register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"pass123","password_confirmation":"pass123"}'
```

### Register Dealer
```bash
curl -X POST http://your-domain.com/api/seller/store-register \
  -H "Content-Type: application/json" \
  -d '{"name":"Showroom","email":"showroom@example.com","password":"pass123","password_confirmation":"pass123","phone":"+123","address":"Address"}'
```

### Register Mediator
```bash
curl -X POST http://your-domain.com/api/mediator/store-register \
  -H "Content-Type: application/json" \
  -d '{"name":"Mediator","email":"mediator@example.com","password":"pass123","password_confirmation":"pass123","phone":"+123","address":"Address","showroom_id":1}'
```

### Verify Email
```bash
curl -X POST http://your-domain.com/api/user-verification \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","otp":"123456"}'
```

### Login
```bash
curl -X POST http://your-domain.com/api/store-login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"pass123"}'
```

---

## JavaScript Examples

### Register
```javascript
fetch('/api/store-register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'John',
    email: 'john@example.com',
    password: 'pass123',
    password_confirmation: 'pass123'
  })
})
```

### Login
```javascript
fetch('/api/store-login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'pass123'
  })
})
.then(res => res.json())
.then(data => {
  localStorage.setItem('token', data.access_token);
  localStorage.setItem('user_type', data.user_type);
});
```

---

## Common Errors

| Error | Solution |
|-------|----------|
| "Email already exist" | Use different email |
| "Invalid token or email" | Check OTP, resend if needed |
| "Showroom not found" | Use valid showroom_id or leave empty |
| "Invalid credentials" | Check email/password, verify email first |

---

**Full documentation: `DOKUMENTASI_REGISTRASI.md`**




