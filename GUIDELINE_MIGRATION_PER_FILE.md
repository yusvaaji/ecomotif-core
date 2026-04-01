# Guideline: Menjalankan Migration Per File

## ⚠️ PENTING: Jangan Jalankan Migration Otomatis

Karena menggunakan backup restore DB, **JANGAN** menjalankan:

- ❌ `php artisan migrate` (tanpa path)
- ❌ `php artisan migrate:fresh`
- ❌ `php artisan migrate:refresh`
- ❌ `php artisan migrate:rollback` (tanpa step)

---

## ✅ Cara Menjalankan Migration Per File

### Step 1: Review Migration File

Sebelum menjalankan, pastikan untuk review migration file untuk memastikan:
- Field yang ditambahkan sudah sesuai kebutuhan
- Tidak ada conflict dengan field yang sudah ada
- Migration menggunakan `Schema::hasColumn()` check untuk safety

### Step 2: Jalankan Migration Satu Per Satu

Gunakan command berikut dengan **path lengkap** ke file migration:

```bash
php artisan migrate --path=/database/migrations/NAMA_FILE.php
```

**Contoh:**
```bash
php artisan migrate --path=/database/migrations/2025_12_14_164432_add_is_mediator_to_users_table.php
```

### Step 3: Verifikasi Setelah Setiap Migration

Setelah migration berhasil, verifikasi dengan:

1. **Check di Database:**
   ```sql
   -- Untuk migration 1
   DESCRIBE users;
   -- Pastikan field is_mediator, showroom_id, barcode sudah ada
   
   -- Untuk migration 2
   DESCRIBE bookings;
   -- Pastikan semua field baru sudah ada
   
   -- Untuk migration 3
   SHOW TABLES LIKE 'showroom_barcodes';
   -- Pastikan tabel sudah dibuat
   ```

2. **Atau menggunakan Laravel Tinker:**
   ```bash
   php artisan tinker
   ```
   ```php
   // Check users table
   Schema::hasColumn('users', 'is_mediator');
   Schema::hasColumn('users', 'showroom_id');
   Schema::hasColumn('users', 'barcode');
   
   // Check bookings table
   Schema::hasColumn('bookings', 'application_type');
   Schema::hasColumn('bookings', 'down_payment');
   // ... dst
   ```

---

## 📋 Urutan Eksekusi Migration

### Migration 1: Add is_mediator to users table

**File:** `database/migrations/2025_12_14_164432_add_is_mediator_to_users_table.php`

**Command:**
```bash
php artisan migrate --path=/database/migrations/2025_12_14_164432_add_is_mediator_to_users_table.php
```

**Verifikasi:**
```sql
DESCRIBE users;
-- Check apakah field berikut sudah ada:
-- - is_mediator (tinyint(1), default 0)
-- - showroom_id (int, nullable)
-- - barcode (varchar, nullable)
```

**Jika Error:**
- Jika field sudah ada: Migration akan skip (karena ada `Schema::hasColumn()` check)
- Jika tabel tidak ada: Pastikan tabel `users` sudah ada di database
- Jika error lain: Check error message dan fix sesuai kebutuhan

---

### Migration 2: Modify bookings for leasing

**File:** `database/migrations/2025_12_14_164454_modify_bookings_for_leasing.php`

**Command:**
```bash
php artisan migrate --path=/database/migrations/2025_12_14_164454_modify_bookings_for_leasing.php
```

**Verifikasi:**
```sql
DESCRIBE bookings;
-- Check apakah field berikut sudah ada:
-- - application_type (enum: 'rental', 'leasing')
-- - down_payment (decimal(10,2), nullable)
-- - installment_amount (decimal(10,2), nullable)
-- - mediator_id (int, nullable)
-- - marketing_id (int, nullable)
-- - showroom_id (int, nullable)
-- - leasing_status (enum: 'pending', 'review', 'approved', 'rejected', 'appealed', nullable)
-- - leasing_notes (text, nullable)
-- - application_documents (json, nullable)
-- - pooled_at (timestamp, nullable)
-- - appealed_at (timestamp, nullable)
```

**Jika Error:**
- Jika tabel `bookings` tidak ada: Buat tabel terlebih dahulu atau skip migration ini
- Jika field sudah ada: Migration akan skip (karena ada `Schema::hasColumn()` check)
- Jika error enum: Pastikan MySQL/MariaDB version support enum

---

### Migration 3: Create showroom_barcodes table (Optional)

**File:** `database/migrations/2025_12_14_164519_create_showroom_barcodes_table.php`

**Command:**
```bash
php artisan migrate --path=/database/migrations/2025_12_14_164519_create_showroom_barcodes_table.php
```

**Verifikasi:**
```sql
SHOW TABLES LIKE 'showroom_barcodes';
DESCRIBE showroom_barcodes;
-- Check apakah tabel sudah dibuat dengan field:
-- - id (bigint, primary key)
-- - user_id (int)
-- - barcode (varchar, unique, nullable)
-- - qr_code (varchar, unique, nullable)
-- - is_active (tinyint(1), default 1)
-- - timestamps
```

**Jika Error:**
- Jika tabel sudah ada: Migration akan skip (karena ada `Schema::hasTable()` check)
- Jika foreign key error: Pastikan tabel `users` sudah ada

---

## 🔧 Troubleshooting

### Error: "Table doesn't exist"

**Problem:** Migration mencoba modify tabel yang belum ada.

**Solution:**
1. Check apakah tabel sudah ada di database
2. Jika belum ada, buat tabel terlebih dahulu atau skip migration
3. Untuk tabel `bookings`, pastikan migration create bookings sudah dijalankan sebelumnya

### Error: "Column already exists"

**Problem:** Field yang ditambahkan sudah ada di tabel.

**Solution:**
- Migration sudah menggunakan `Schema::hasColumn()` check, jadi seharusnya tidak error
- Jika tetap error, berarti field sudah ada dan migration akan skip
- Check manual di database untuk memastikan

### Error: "Syntax error or access violation"

**Problem:** Ada syntax error di migration atau permission issue.

**Solution:**
1. Check syntax migration file
2. Pastikan database user punya permission untuk ALTER TABLE
3. Check MySQL/MariaDB version compatibility

### Error: "Foreign key constraint fails"

**Problem:** Foreign key reference ke tabel yang belum ada atau data tidak valid.

**Solution:**
1. Pastikan tabel yang direferensi sudah ada
2. Check data di tabel yang direferensi
3. Untuk `showroom_barcodes.user_id`, pastikan ada data di tabel `users` dengan `is_dealer = 1`

---

## 🔄 Rollback (Jika Perlu)

Jika perlu rollback migration tertentu:

```bash
# Rollback 1 step terakhir
php artisan migrate:rollback --step=1

# Rollback semua migration
php artisan migrate:rollback
```

**⚠️ HATI-HATI:** Rollback akan menghapus field/tabel yang dibuat migration. Pastikan tidak ada data penting yang akan hilang.

---

## ✅ Checklist Setelah Migration

Setelah semua migration berhasil:

- [ ] Field `is_mediator`, `showroom_id`, `barcode` sudah ada di tabel `users`
- [ ] Semua field baru sudah ada di tabel `bookings`
- [ ] Tabel `showroom_barcodes` sudah dibuat (jika migration 3 dijalankan)
- [ ] Test create user dengan `is_mediator = 1`
- [ ] Test create booking dengan `application_type = 'leasing'`
- [ ] Test relationship di model (User->mediatorApplications, Booking->mediator, dll)

---

## 📝 Notes

1. **Backup Database:** Sebelum menjalankan migration, pastikan sudah backup database
2. **Test Environment:** Test migration di development/staging terlebih dahulu
3. **Production:** Untuk production, schedule maintenance window dan backup database sebelum migration
4. **Monitoring:** Monitor error log setelah migration untuk memastikan tidak ada issue

---

**Selamat! Migration selesai dijalankan per file sesuai guideline.**
