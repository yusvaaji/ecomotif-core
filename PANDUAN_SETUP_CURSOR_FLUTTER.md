# Panduan Setup Cursor untuk Edit Flutter App - Step by Step

## 🎯 Overview

Panduan lengkap dari install tools sampai bisa edit Flutter app di Cursor IDE.

---

## 📋 Step 1: Install Prerequisites

### 1.1 Install Flutter SDK

#### Windows:
1. **Download Flutter SDK:**
   - Kunjungi: https://flutter.dev/docs/get-started/install/windows
   - Download Flutter SDK (zip file)
   - Extract ke folder, contoh: `C:\src\flutter`

2. **Add Flutter to PATH:**
   - Buka "Environment Variables"
   - Edit "Path" variable
   - Tambahkan: `C:\src\flutter\bin`

3. **Verify Installation:**
   ```bash
   flutter --version
   flutter doctor
   ```

#### Mac/Linux:
```bash
# Download Flutter
git clone https://github.com/flutter/flutter.git -b stable
cd flutter
export PATH="$PATH:`pwd`/bin"

# Verify
flutter --version
flutter doctor
```

### 1.2 Install Android Studio (Untuk Android Development)

1. **Download Android Studio:**
   - Kunjungi: https://developer.android.com/studio
   - Download dan install

2. **Setup Android SDK:**
   - Buka Android Studio
   - Go to: Tools → SDK Manager
   - Install:
     - Android SDK Platform
     - Android SDK Build-Tools
     - Android SDK Command-line Tools

3. **Setup Android Emulator (Optional):**
   - Go to: Tools → Device Manager
   - Create Virtual Device
   - Pilih device (contoh: Pixel 5)
   - Download system image
   - Finish

### 1.3 Install Xcode (Hanya untuk Mac - iOS Development)

1. **Install Xcode dari App Store**
2. **Install Command Line Tools:**
   ```bash
   xcode-select --install
   ```
3. **Install CocoaPods:**
   ```bash
   sudo gem install cocoapods
   ```

### 1.4 Install Git

1. **Download Git:**
   - Windows: https://git-scm.com/download/win
   - Mac: `brew install git`
   - Linux: `sudo apt-get install git`

2. **Verify:**
   ```bash
   git --version
   ```

### 1.5 Install VS Code (Optional - untuk Flutter extension)

1. **Download VS Code:**
   - https://code.visualstudio.com/

2. **Install Flutter Extension:**
   - Buka VS Code
   - Go to Extensions (Ctrl+Shift+X)
   - Search "Flutter"
   - Install "Flutter" extension (by Dart Code)

---

## 📦 Step 2: Install Cursor IDE

### 2.1 Download Cursor

1. **Kunjungi:** https://cursor.sh/
2. **Download Cursor** untuk OS Anda
3. **Install Cursor** seperti aplikasi biasa

### 2.2 Setup Cursor

1. **Buka Cursor**
2. **Login atau Sign Up** (jika diperlukan)
3. **Install Flutter/Dart Extensions:**
   - Buka Extensions (Ctrl+Shift+X atau Cmd+Shift+X)
   - Search "Flutter"
   - Install "Flutter" extension
   - Install "Dart" extension (biasanya auto-install dengan Flutter)

---

## 🔧 Step 3: Setup Flutter Project

### 3.1 Buka Project di Cursor

1. **Buka Cursor**
2. **File → Open Folder** (atau Ctrl+K Ctrl+O)
3. **Navigate ke folder Flutter project:**
   ```
   C:\Grafik\UMAR\ecomotif\mainv2\main_files\mobile\main_files\source_code\CarBaz_flutter-main
   ```
4. **Klik "Select Folder"**

### 3.2 Install Flutter Dependencies

1. **Buka Terminal di Cursor:**
   - Terminal → New Terminal (Ctrl+` atau Cmd+`)

2. **Run command:**
   ```bash
   flutter pub get
   ```

3. **Verify Flutter Setup:**
   ```bash
   flutter doctor
   ```
   Pastikan semua checklist hijau (atau minimal Android/iOS setup OK)

### 3.3 Setup FVM (Flutter Version Manager) - Optional

Jika project menggunakan FVM (ada file `.fvmrc`):

1. **Install FVM:**
   ```bash
   # Windows (PowerShell)
   dart pub global activate fvm
   
   # Mac/Linux
   pub global activate fvm
   ```

2. **Install Flutter Version:**
   ```bash
   fvm install stable
   fvm use stable
   ```

3. **Verify:**
   ```bash
   fvm flutter --version
   ```

---

## 🎨 Step 4: Configure Cursor untuk Flutter

### 4.1 Install Extensions

**Di Cursor Extensions, install:**

1. **Flutter** (by Dart Code)
   - Auto-complete untuk Flutter
   - Hot reload support
   - Debugging support

2. **Dart** (by Dart Code)
   - Syntax highlighting
   - Code formatting

3. **Error Lens** (Optional)
   - Show errors inline

4. **Flutter Widget Snippets** (Optional)
   - Quick snippets untuk Flutter widgets

### 4.2 Configure Settings

1. **Buka Settings:**
   - File → Preferences → Settings (Ctrl+, atau Cmd+,)

2. **Search "dart"** dan configure:
   - ✅ Enable Dart analysis server
   - ✅ Format on save
   - ✅ Enable linting

3. **Search "flutter"** dan configure:
   - ✅ Enable Flutter SDK path (auto-detect)
   - ✅ Enable hot reload on save

---

## 🚀 Step 5: Test Setup

### 5.1 Check Flutter Project

1. **Buka terminal di Cursor**
2. **Run:**
   ```bash
   flutter doctor
   flutter pub get
   ```

3. **Check jika ada errors:**
   - Lihat di Problems panel (Ctrl+Shift+M)
   - Fix errors jika ada

### 5.2 Run App (Test)

#### Android:
```bash
# List devices
flutter devices

# Run di emulator/device
flutter run

# Atau run di specific device
flutter run -d <device-id>
```

#### iOS (Mac only):
```bash
flutter run -d ios
```

### 5.3 Verify Hot Reload

1. **Run app** (flutter run)
2. **Edit file** (contoh: ubah text di screen)
3. **Save file** (Ctrl+S)
4. **Hot reload** otomatis atau tekan `r` di terminal
5. **Check perubahan** di emulator/device

---

## ✏️ Step 6: Mulai Edit Project

### 6.1 Struktur File yang Sering Diedit

#### A. API Configuration
**File:** `lib/data/data_provider/remote_url.dart`
```dart
// Edit base URL di sini
static const String rootUrl = "https://your-domain.com/";
```

#### B. App Configuration
**File:** `lib/utils/k_string.dart`
```dart
// Edit app name, strings
static const String appName = "Your App Name";
```

#### C. Theme/Colors
**File:** `lib/widgets/custom_theme.dart`
```dart
// Edit theme colors
static const Color primaryColor = Color(0xFFYourColor);
```

#### D. Screens
**Folder:** `lib/presentation/screen/`
- Edit existing screens
- Buat screens baru

#### E. Models
**Folder:** `lib/data/model/`
- Edit data models
- Buat models baru

### 6.2 Tips Edit di Cursor

1. **Use AI Assistant:**
   - Tekan `Ctrl+L` (Windows) atau `Cmd+L` (Mac)
   - Tanya tentang code atau minta generate code

2. **Quick Fix:**
   - Hover di error → klik lightbulb icon
   - Atau `Ctrl+.` untuk quick fix menu

3. **Navigate:**
   - `Ctrl+P` - Quick file open
   - `Ctrl+Shift+F` - Search in all files
   - `F12` - Go to definition
   - `Shift+F12` - Find all references

4. **Format Code:**
   - `Shift+Alt+F` (Windows) atau `Shift+Option+F` (Mac)
   - Atau right-click → Format Document

---

## 🔍 Step 7: Debugging

### 7.1 Setup Debug Configuration

1. **Buka Run & Debug panel:**
   - `Ctrl+Shift+D` atau klik icon Debug di sidebar

2. **Create launch.json:**
   - Klik "create a launch.json file"
   - Pilih "Dart & Flutter"
   - File akan dibuat di `.vscode/launch.json`

3. **Debug configurations tersedia:**
   - Flutter: Launch (main.dart)
   - Flutter: Attach to Process

### 7.2 Run Debug

1. **Set breakpoint:**
   - Klik di line number (red dot muncul)

2. **Start debugging:**
   - Tekan `F5` atau klik "Start Debugging"
   - App akan run dan stop di breakpoint

3. **Debug tools:**
   - Variables panel - lihat variable values
   - Watch panel - monitor specific variables
   - Call Stack - lihat function calls
   - Debug Console - run commands

---

## 📱 Step 8: Connect Device/Emulator

### 8.1 Android Emulator

1. **Start Android Studio**
2. **Open Device Manager**
3. **Start emulator** (klik play button)
4. **Di Cursor terminal:**
   ```bash
   flutter devices
   # Should show emulator
   ```

### 8.2 Physical Android Device

1. **Enable Developer Options:**
   - Settings → About Phone
   - Tap "Build Number" 7 times

2. **Enable USB Debugging:**
   - Settings → Developer Options
   - Enable "USB Debugging"

3. **Connect via USB:**
   - Connect phone ke PC
   - Accept USB debugging prompt di phone
   - Run: `flutter devices`

### 8.3 iOS Simulator (Mac only)

1. **Open Simulator:**
   ```bash
   open -a Simulator
   ```

2. **Or dari Xcode:**
   - Xcode → Open Developer Tool → Simulator

3. **Run:**
   ```bash
   flutter devices
   flutter run -d ios
   ```

---

## 🛠️ Step 9: Common Tasks

### 9.1 Add New Package

1. **Edit `pubspec.yaml`:**
   ```yaml
   dependencies:
     new_package: ^1.0.0
   ```

2. **Run:**
   ```bash
   flutter pub get
   ```

3. **Import di code:**
   ```dart
   import 'package:new_package/new_package.dart';
   ```

### 9.2 Create New Screen

1. **Create file:**
   - Right-click di `lib/presentation/screen/`
   - New File → `new_screen.dart`

2. **Create widget:**
   ```dart
   class NewScreen extends StatelessWidget {
     @override
     Widget build(BuildContext context) {
       return Scaffold(
         appBar: AppBar(title: Text('New Screen')),
         body: Center(child: Text('Hello')),
       );
     }
   }
   ```

3. **Add route:**
   - Edit `lib/routes/route_names.dart`
   - Add route constant
   - Add route di `generateRoutes()`

### 9.3 Update API Endpoint

1. **Open:** `lib/data/data_provider/remote_url.dart`
2. **Add new endpoint:**
   ```dart
   static const String newEndpoint = '${baseUrl}new-endpoint';
   ```
3. **Use di repository:**
   ```dart
   final response = await remoteDataSource.get(RemoteUrls.newEndpoint);
   ```

---

## 🐛 Step 10: Troubleshooting

### Issue 1: Flutter Not Found

**Solution:**
```bash
# Check Flutter path
echo $PATH  # Mac/Linux
echo %PATH%  # Windows

# Add Flutter to PATH
# Windows: Add to System Environment Variables
# Mac/Linux: Add to ~/.bashrc or ~/.zshrc
export PATH="$PATH:/path/to/flutter/bin"
```

### Issue 2: Dependencies Error

**Solution:**
```bash
flutter clean
flutter pub get
```

### Issue 3: Cursor Not Recognizing Flutter

**Solution:**
1. Restart Cursor
2. Check Flutter extension installed
3. Check Flutter SDK path in settings
4. Run: `flutter doctor` to verify setup

### Issue 4: Hot Reload Not Working

**Solution:**
1. Check app is running (`flutter run`)
2. Press `r` in terminal for hot reload
3. Press `R` for hot restart
4. Check for errors in Problems panel

### Issue 5: Build Errors

**Solution:**
```bash
# Android
cd android
./gradlew clean
cd ..
flutter clean
flutter pub get
flutter run

# iOS
cd ios
pod deintegrate
pod install
cd ..
flutter clean
flutter pub get
flutter run
```

---

## 📋 Quick Reference Commands

### Flutter Commands:
```bash
# Check setup
flutter doctor

# Get dependencies
flutter pub get

# Run app
flutter run

# Build release
flutter build apk          # Android APK
flutter build appbundle    # Android Bundle
flutter build ios          # iOS

# Clean
flutter clean

# List devices
flutter devices
```

### Cursor Shortcuts:
```
Ctrl+P          - Quick file open
Ctrl+Shift+F    - Search in files
Ctrl+L          - AI Assistant
F12             - Go to definition
Shift+F12       - Find references
Ctrl+.          - Quick fix
Shift+Alt+F     - Format code
F5              - Start debugging
Ctrl+`          - Toggle terminal
```

---

## ✅ Checklist Setup

### Installation:
- [ ] Flutter SDK installed
- [ ] Android Studio installed (untuk Android)
- [ ] Xcode installed (untuk iOS - Mac only)
- [ ] Git installed
- [ ] Cursor IDE installed

### Cursor Setup:
- [ ] Flutter extension installed
- [ ] Dart extension installed
- [ ] Project opened di Cursor
- [ ] Dependencies installed (`flutter pub get`)
- [ ] Flutter doctor passed

### Testing:
- [ ] App bisa run di emulator/device
- [ ] Hot reload working
- [ ] Debugging working
- [ ] Bisa edit dan save files

---

## 🎯 Next Steps

Setelah setup selesai:

1. **Update API Base URL:**
   - Edit `lib/data/data_provider/remote_url.dart`

2. **Test API Connection:**
   - Run app
   - Check jika API calls berhasil

3. **Start Customizing:**
   - Ikuti `PANDUAN_CUSTOMIZE_FLUTTER_APP.md`
   - Implement fitur baru sesuai kebutuhan

---

## 📚 Resources

- **Flutter Docs:** https://flutter.dev/docs
- **Cursor Docs:** https://cursor.sh/docs
- **Dart Docs:** https://dart.dev/guides
- **Flutter Packages:** https://pub.dev/

---

**Setup selesai! Sekarang Anda bisa edit Flutter app di Cursor dengan nyaman.**




