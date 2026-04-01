# Fix Flutter PATH di Windows - Step by Step

## 🔍 Step 1: Cari Lokasi Flutter

Flutter biasanya diinstall di salah satu lokasi ini:
- `C:\src\flutter`
- `C:\flutter`
- `C:\Users\YourName\flutter`
- `C:\Users\YourName\Downloads\flutter`

### Cara Cek:

**Di PowerShell, jalankan satu per satu:**

```powershell
# Cek di lokasi umum
Test-Path "C:\src\flutter\bin\flutter.bat"
Test-Path "C:\flutter\bin\flutter.bat"
Test-Path "$env:USERPROFILE\flutter\bin\flutter.bat"
Test-Path "$env:USERPROFILE\Downloads\flutter\bin\flutter.bat"

# Atau cari di semua drive
Get-ChildItem -Path C:\ -Filter "flutter.bat" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1 FullName
```

**Atau cek manual:**
1. Buka File Explorer
2. Cari folder "flutter" di C:\
3. Atau cek di Downloads folder

---

## ✅ Step 2: Tambahkan Flutter ke PATH

### Method 1: Via System Environment Variables (Permanent)

1. **Buka System Properties:**
   - Tekan `Windows + R`
   - Ketik: `sysdm.cpl`
   - Enter

2. **Buka Environment Variables:**
   - Klik tab "Advanced"
   - Klik "Environment Variables"

3. **Edit PATH:**
   - Di "System variables", cari "Path"
   - Klik "Edit"
   - Klik "New"
   - Tambahkan path ke Flutter bin folder:
     ```
     C:\src\flutter\bin
     ```
     (Ganti dengan path Flutter Anda yang sebenarnya)
   - Klik "OK" di semua dialog

4. **Restart Terminal/PowerShell:**
   - Tutup semua terminal/PowerShell
   - Buka PowerShell baru
   - Test: `flutter --version`

### Method 2: Via PowerShell (Temporary - Hanya untuk session ini)

**Jalankan di PowerShell:**

```powershell
# Ganti path dengan lokasi Flutter Anda
$flutterPath = "C:\src\flutter\bin"
$env:PATH += ";$flutterPath"

# Test
flutter --version
```

**Note:** Method ini hanya untuk session saat ini. Setelah tutup PowerShell, PATH akan hilang.

### Method 3: Via PowerShell (Permanent - Recommended)

**Jalankan PowerShell sebagai Administrator:**

```powershell
# Ganti path dengan lokasi Flutter Anda
$flutterPath = "C:\src\flutter\bin"

# Tambahkan ke System PATH
[Environment]::SetEnvironmentVariable(
    "Path",
    [Environment]::GetEnvironmentVariable("Path", "Machine") + ";$flutterPath",
    "Machine"
)

# Refresh PATH di session ini
$env:PATH = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Test
flutter --version
```

---

## 🔧 Step 3: Verify Installation

Setelah menambahkan ke PATH, **restart PowerShell** dan test:

```powershell
# Test Flutter
flutter --version

# Check Flutter setup
flutter doctor
```

---

## 🐛 Troubleshooting

### Issue 1: Masih "flutter is not recognized"

**Solution:**
1. Pastikan path yang ditambahkan benar (harus ke folder `bin`)
2. Restart PowerShell/terminal
3. Restart komputer (jika perlu)
4. Cek PATH lagi:
   ```powershell
   $env:PATH -split ';' | Select-String -Pattern "flutter"
   ```

### Issue 2: Flutter tidak ditemukan di sistem

**Solution:**
- Download Flutter SDK:
  1. Kunjungi: https://docs.flutter.dev/get-started/install/windows
  2. Download Flutter SDK (zip)
  3. Extract ke `C:\src\flutter` (atau lokasi lain)
  4. Ikuti Step 2 untuk tambahkan ke PATH

### Issue 3: Permission Denied

**Solution:**
- Jalankan PowerShell sebagai Administrator:
  1. Klik kanan PowerShell
  2. Pilih "Run as Administrator"
  3. Jalankan command lagi

---

## 📋 Quick Fix Script

**Copy script ini dan jalankan di PowerShell (sebagai Administrator):**

```powershell
# ============================================
# Flutter PATH Setup Script
# ============================================

# 1. Cari Flutter
Write-Host "Mencari Flutter SDK..." -ForegroundColor Yellow

$flutterPaths = @(
    "C:\src\flutter\bin",
    "C:\flutter\bin",
    "$env:USERPROFILE\flutter\bin",
    "$env:USERPROFILE\Downloads\flutter\bin"
)

$flutterFound = $null
foreach ($path in $flutterPaths) {
    if (Test-Path "$path\flutter.bat") {
        $flutterFound = $path
        Write-Host "Flutter ditemukan di: $flutterFound" -ForegroundColor Green
        break
    }
}

if (-not $flutterFound) {
    Write-Host "Flutter tidak ditemukan!" -ForegroundColor Red
    Write-Host "Silakan download Flutter SDK dari: https://docs.flutter.dev/get-started/install/windows" -ForegroundColor Yellow
    Write-Host "Atau extract Flutter ke salah satu lokasi ini:" -ForegroundColor Yellow
    $flutterPaths | ForEach-Object { Write-Host "  - $_" }
    exit
}

# 2. Tambahkan ke PATH
Write-Host "Menambahkan Flutter ke PATH..." -ForegroundColor Yellow

$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")
if ($currentPath -notlike "*$flutterFound*") {
    [Environment]::SetEnvironmentVariable(
        "Path",
        $currentPath + ";$flutterFound",
        "Machine"
    )
    Write-Host "Flutter berhasil ditambahkan ke PATH!" -ForegroundColor Green
} else {
    Write-Host "Flutter sudah ada di PATH!" -ForegroundColor Green
}

# 3. Refresh PATH di session ini
$env:PATH = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# 4. Test
Write-Host "`nTesting Flutter..." -ForegroundColor Yellow
flutter --version

Write-Host "`nSetup selesai! Silakan restart PowerShell dan jalankan 'flutter doctor'" -ForegroundColor Green
```

---

## ✅ Checklist

- [ ] Flutter SDK sudah di-download dan di-extract
- [ ] Lokasi Flutter diketahui (path ke folder `bin`)
- [ ] Flutter ditambahkan ke System PATH
- [ ] PowerShell/Terminal di-restart
- [ ] `flutter --version` berhasil
- [ ] `flutter doctor` berhasil

---

**Setelah Flutter di PATH, lanjutkan ke Step 2 di `PANDUAN_SETUP_CURSOR_FLUTTER.md`**




