# Instruksi Install Android SDK untuk Build APK

## Catatan Penting
Untuk build APK Android, Android SDK **harus diinstall terlebih dahulu**. Flutter tidak bisa build APK tanpa Android SDK yang lengkap.

## Opsi 1: Install Android Studio (Recommended)

1. **Download Android Studio:**
   - Kunjungi: https://developer.android.com/studio
   - Download installer untuk Windows
   - File size: ~1 GB

2. **Install Android Studio:**
   - Jalankan installer
   - Ikuti wizard instalasi
   - Pastikan "Android SDK" tercentang
   - Pilih lokasi instalasi (default: `C:\Users\[Username]\AppData\Local\Android\Sdk`)

3. **Setup SDK Components:**
   - Buka Android Studio
   - Pilih "Configure" > "SDK Manager"
   - Di tab "SDK Platforms", centang:
     - Android 14.0 (API 34) atau lebih baru
   - Di tab "SDK Tools", pastikan tercentang:
     - Android SDK Build-Tools
     - Android SDK Platform-Tools
     - Android Emulator
   - Klik "Apply" untuk download

4. **Set Environment Variables:**
   - Buka System Properties > Environment Variables
   - Tambahkan User Variable:
     - Name: `ANDROID_HOME`
     - Value: `C:\Users\[Username]\AppData\Local\Android\Sdk`
   - Edit PATH variable, tambahkan:
     - `%ANDROID_HOME%\platform-tools`
     - `%ANDROID_HOME%\tools`

5. **Restart Terminal/Cursor:**
   - Tutup dan buka kembali terminal
   - Jalankan: `flutter doctor` untuk verifikasi

## Opsi 2: Install Command Line Tools Saja (Advanced)

1. **Download Command Line Tools:**
   - Kunjungi: https://developer.android.com/studio#command-tools
   - Download "Command line tools only" untuk Windows
   - Extract ke: `C:\Users\[Username]\AppData\Local\Android\Sdk\cmdline-tools\latest`

2. **Install SDK Components via Command Line:**
   ```powershell
   $sdkPath = "$env:LOCALAPPDATA\Android\Sdk"
   $toolsPath = "$sdkPath\cmdline-tools\latest\bin"
   $env:ANDROID_HOME = $sdkPath
   
   # Accept licenses
   & "$toolsPath\sdkmanager.bat" --licenses
   
   # Install required components
   & "$toolsPath\sdkmanager.bat" "platform-tools" "platforms;android-34" "build-tools;34.0.0"
   ```

3. **Set Environment Variables** (sama seperti Opsi 1)

## Setelah SDK Terinstall

Jalankan build APK:
```powershell
cd mobile/main_files/source_code/CarBaz_flutter-main
flutter build apk --release
```

APK akan tersimpan di:
`build/app/outputs/flutter-apk/app-release.apk`

Untuk copy ke directory mobile:
```powershell
Copy-Item "build/app/outputs/flutter-apk/app-release.apk" -Destination "..\..\..\ECOMOTIF-release.apk"
```

## Troubleshooting

### Error: "No Android SDK found"
- Pastikan `ANDROID_HOME` environment variable sudah diset
- Restart terminal setelah set environment variable
- Jalankan `flutter config --android-sdk [PATH_TO_SDK]`

### Error: "Android licenses not accepted"
- Jalankan: `flutter doctor --android-licenses`
- Tekan `y` untuk accept semua licenses

### Error: "SDK component not found"
- Buka Android Studio > SDK Manager
- Install missing components
- Atau gunakan sdkmanager command line




