# Instruksi Build APK ECOMOTIF

## Masalah Saat Ini
Flutter doctor menunjukkan "Unable to locate Android SDK" karena komponen SDK belum terinstall lengkap. Folder SDK ada, tapi file-file penting seperti `adb.exe`, `build-tools`, dan `platforms` belum terinstall.

## Solusi: Install Android SDK Components

### Opsi 1: Menggunakan Script Otomatis (Recommended)

1. **Buka PowerShell sebagai Administrator:**
   - Klik kanan pada PowerShell
   - Pilih "Run as Administrator"

2. **Jalankan script install:**
   ```powershell
   cd C:\Grafik\UMAR\ecomotif\mainv2\main_files\mobile
   .\install_android_sdk_components.ps1
   ```

3. **Tunggu proses download dan install selesai** (10-30 menit, tergantung koneksi internet)

4. **Restart Cursor/Terminal**

5. **Verifikasi:**
   ```powershell
   flutter doctor
   ```
   Seharusnya Android toolchain sekarang menunjukkan ✓

6. **Build APK:**
   ```powershell
   cd main_files\source_code\CarBaz_flutter-main
   flutter build apk --release
   ```

7. **Copy APK ke folder mobile:**
   ```powershell
   Copy-Item "build\app\outputs\flutter-apk\app-release.apk" -Destination "..\..\..\ECOMOTIF-release.apk"
   ```

### Opsi 2: Install Android Studio (Lebih Mudah)

1. **Download Android Studio:**
   - Kunjungi: https://developer.android.com/studio
   - Download installer (sekitar 1 GB)

2. **Install Android Studio:**
   - Jalankan installer
   - Ikuti wizard instalasi
   - Pastikan "Android SDK" tercentang

3. **Setup SDK di Android Studio:**
   - Buka Android Studio
   - Pilih "Configure" > "SDK Manager"
   - Di tab "SDK Platforms", centang:
     - ✅ Android 14.0 (API 34)
   - Di tab "SDK Tools", pastikan tercentang:
     - ✅ Android SDK Build-Tools
     - ✅ Android SDK Platform-Tools
     - ✅ Android Emulator
   - Klik "Apply" dan tunggu download selesai

4. **Set Environment Variable:**
   - Buka System Properties > Environment Variables
   - Tambahkan User Variable:
     - Name: `ANDROID_HOME`
     - Value: `C:\Users\[Username]\AppData\Local\Android\Sdk`
   - Edit PATH, tambahkan:
     - `%ANDROID_HOME%\platform-tools`
     - `%ANDROID_HOME%\tools`

5. **Restart Cursor/Terminal**

6. **Verifikasi dan Build:**
   ```powershell
   flutter doctor
   cd mobile\main_files\source_code\CarBaz_flutter-main
   flutter build apk --release
   ```

### Opsi 3: Manual Install via Command Line Tools

Jika script tidak bekerja, install manual:

1. **Download Command Line Tools:**
   - https://developer.android.com/studio#command-tools
   - Extract ke: `C:\Users\[Username]\AppData\Local\Android\Sdk\cmdline-tools\latest`

2. **Install components:**
   ```powershell
   $sdkPath = "$env:LOCALAPPDATA\Android\Sdk"
   $sdkmanager = "$sdkPath\cmdline-tools\latest\bin\sdkmanager.bat"
   
   # Accept licenses
   echo "y" | & $sdkmanager --sdk_root=$sdkPath --licenses
   
   # Install components
   & $sdkmanager --sdk_root=$sdkPath "platform-tools" "platforms;android-34" "build-tools;34.0.0"
   ```

## Setelah SDK Terinstall

Jalankan build:
```powershell
cd C:\Grafik\UMAR\ecomotif\mainv2\main_files\mobile\main_files\source_code\CarBaz_flutter-main
flutter build apk --release
```

APK akan tersimpan di:
- `build\app\outputs\flutter-apk\app-release.apk`

Untuk copy ke folder mobile:
```powershell
Copy-Item "build\app\outputs\flutter-apk\app-release.apk" -Destination "C:\Grafik\UMAR\ecomotif\mainv2\main_files\mobile\ECOMOTIF-release.apk"
```

## Troubleshooting

### Error: "No Android SDK found"
- Pastikan `ANDROID_HOME` environment variable sudah diset
- Restart terminal setelah set environment variable
- Jalankan: `flutter config --android-sdk [PATH_TO_SDK]`

### Error: "SDK component not found"
- Pastikan semua komponen sudah terinstall (platform-tools, build-tools, platforms)
- Cek dengan: `Test-Path "$env:LOCALAPPDATA\Android\Sdk\platform-tools\adb.exe"`

### Build Error: "Gradle sync failed"
- Pastikan build-tools sudah terinstall
- Cek versi build-tools di `android/app/build.gradle`

### Download Error
- Pastikan koneksi internet stabil
- Coba gunakan VPN jika ada masalah dengan Google servers
- Atau install Android Studio yang akan handle download otomatis




