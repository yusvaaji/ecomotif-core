# Quick Start - Setup Cursor untuk Flutter App

## ⚡ Quick Setup (5 Menit)

### 1. Install Flutter
```bash
# Download dari: https://flutter.dev/docs/get-started/install
# Extract dan add ke PATH
flutter doctor
```

### 2. Install Cursor
- Download: https://cursor.sh/
- Install seperti aplikasi biasa

### 3. Install Extensions di Cursor
- Buka Extensions (Ctrl+Shift+X)
- Install "Flutter" extension
- Install "Dart" extension (auto)

### 4. Buka Project
```
File → Open Folder
→ Navigate ke: mobile/main_files/source_code/CarBaz_flutter-main
```

### 5. Install Dependencies
```bash
# Di terminal Cursor (Ctrl+`)
flutter pub get
```

### 6. Run App
```bash
flutter run
```

---

## 🎯 File Penting untuk Edit

### API Configuration:
```
lib/data/data_provider/remote_url.dart
```
**Edit base URL di sini!**

### App Name:
```
lib/utils/k_string.dart
```

### Theme/Colors:
```
lib/widgets/custom_theme.dart
```

### Screens:
```
lib/presentation/screen/
```

---

## 🔥 Cursor Shortcuts

- `Ctrl+L` - AI Assistant
- `Ctrl+P` - Quick file open
- `F12` - Go to definition
- `Ctrl+.` - Quick fix
- `Shift+Alt+F` - Format code
- `F5` - Debug

---

## ✅ Checklist

- [ ] Flutter installed (`flutter doctor` OK)
- [ ] Cursor installed
- [ ] Flutter extension installed
- [ ] Project opened
- [ ] `flutter pub get` success
- [ ] App bisa run

---

**Detail lengkap: Lihat `PANDUAN_SETUP_CURSOR_FLUTTER.md`**




