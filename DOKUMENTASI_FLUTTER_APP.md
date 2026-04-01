# Dokumentasi Flutter App - CarBaz Mobile Application

## Overview

Aplikasi Flutter untuk platform CarBaz yang terintegrasi dengan Laravel backend API. Aplikasi ini menggunakan arsitektur **BLoC (Business Logic Component)** pattern dengan dependency injection.

---

## 📁 Struktur Folder Project

```
CarBaz_flutter-main/
├── android/                    # Android native configuration
│   ├── app/
│   │   ├── build.gradle        # Android build configuration
│   │   └── src/                 # Android source files
│   ├── build.gradle
│   └── gradle/                 # Gradle wrapper
│
├── ios/                        # iOS native configuration
│   ├── Runner/                 # iOS app files
│   ├── Podfile                 # CocoaPods dependencies
│   └── Runner.xcodeproj/       # Xcode project
│
├── lib/                        # 🎯 MAIN FLUTTER CODE
│   ├── main.dart               # Entry point aplikasi
│   ├── dependency_injection.dart  # Dependency injection setup
│   │
│   ├── data/                   # Data Layer
│   │   ├── data_provider/      # API & Local data sources
│   │   │   ├── remote_data_source.dart    # API calls
│   │   │   ├── remote_url.dart             # API endpoints
│   │   │   ├── local_data_source.dart      # Local storage
│   │   │   └── network_parser.dart         # Response parser
│   │   ├── model/              # Data models
│   │   │   ├── auth/           # Authentication models
│   │   │   ├── car/            # Car models
│   │   │   ├── booking_history/
│   │   │   ├── dashboard/
│   │   │   ├── home/
│   │   │   ├── kyc/
│   │   │   ├── payment_model/
│   │   │   ├── review/
│   │   │   ├── subscription/
│   │   │   └── ...
│   │   └── dummy_data/         # Dummy data for testing
│   │
│   ├── logic/                  # Business Logic Layer
│   │   ├── bloc/               # BLoC implementations
│   │   │   ├── login/          # Login BLoC
│   │   │   └── internet_status/ # Internet status BLoC
│   │   ├── cubit/              # Cubit implementations (simpler BLoC)
│   │   │   ├── all_cars/       # All cars cubit
│   │   │   ├── home/           # Home screen cubit
│   │   │   ├── profile/        # Profile cubit
│   │   │   ├── manage_car/     # Car management cubit
│   │   │   ├── subscription/  # Subscription cubit
│   │   │   └── ...
│   │   └── repository/         # Repository implementations
│   │       ├── auth_repository.dart
│   │       ├── home_repository.dart
│   │       ├── wishlist_repository.dart
│   │       └── ...
│   │
│   ├── presentation/           # UI Layer
│   │   ├── screen/             # All screens
│   │   │   ├── splash_screen/  # Splash screen
│   │   │   ├── on_boarding/     # Onboarding screens
│   │   │   ├── authentication/ # Login, Register, OTP
│   │   │   ├── home/           # Home screen & components
│   │   │   ├── details_screen/ # Car details
│   │   │   ├── manage_car/    # Add/Edit car
│   │   │   ├── profile/        # User profile
│   │   │   ├── payment/        # Payment screens
│   │   │   ├── kyc/            # KYC verification
│   │   │   └── ...
│   │   └── errors/             # Error handling
│   │
│   ├── routes/                 # Navigation routes
│   │   └── route_names.dart    # Route definitions
│   │
│   ├── utils/                  # Utilities
│   │   ├── constraints.dart    # Constants
│   │   ├── k_string.dart       # String constants
│   │   ├── k_images.dart       # Image paths
│   │   └── ...
│   │
│   └── widgets/                # Reusable widgets
│       ├── custom_app_bar.dart
│       ├── custom_button.dart
│       ├── custom_dialog.dart
│       └── ...
│
├── assets/                     # Assets (images, icons)
│   ├── icons/                  # SVG icons
│   ├── images/                 # Images (PNG, JPG, SVG)
│   └── luncher_icon.png        # App launcher icon
│
├── web/                        # Web configuration
├── windows/                    # Windows configuration
├── linux/                      # Linux configuration
├── macos/                      # macOS configuration
│
├── pubspec.yaml                # 📦 Dependencies & configuration
├── pubspec.lock                # Locked dependencies
├── analysis_options.yaml       # Linter configuration
└── README.md                   # Project README
```

---

## 🏗️ Arsitektur Aplikasi

### Pattern: **BLoC (Business Logic Component)**

```
┌─────────────────────────────────────────┐
│         PRESENTATION LAYER               │
│  (Screens, Widgets, UI Components)       │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         BUSINESS LOGIC LAYER             │
│  (BLoC/Cubit - State Management)         │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         DATA LAYER                       │
│  (Repository → Data Source)              │
│  ├── Remote Data Source (API)            │
│  └── Local Data Source (SharedPrefs)    │
└─────────────────────────────────────────┘
```

### Flow Data:
1. **UI** → Trigger event ke **BLoC/Cubit**
2. **BLoC/Cubit** → Panggil **Repository**
3. **Repository** → Panggil **Data Source** (API/Local)
4. **Data Source** → Return data ke **Repository**
5. **Repository** → Return data ke **BLoC/Cubit**
6. **BLoC/Cubit** → Emit state baru
7. **UI** → Rebuild berdasarkan state baru

---

## 📦 Dependencies (pubspec.yaml)

### Core Dependencies:
- **flutter_bloc: ^9.1.0** - State management (BLoC pattern)
- **http: ^1.2.2** - HTTP client untuk API calls
- **shared_preferences: ^2.3.3** - Local storage
- **connectivity_plus: ^6.1.0** - Network connectivity check

### UI Dependencies:
- **flutter_screenutil: ^5.9.3** - Responsive UI
- **google_fonts: ^6.2.1** - Custom fonts
- **cached_network_image: ^3.4.1** - Image caching
- **flutter_svg: ^2.0.14** - SVG support
- **carousel_slider: ^5.0.0** - Image carousel

### Form & Validation:
- **pinput: ^5.0.0** - PIN input widget
- **form_builder_validators: ^11.0.0** - Form validation

### Payment & WebView:
- **webview_flutter: ^4.10.0** - WebView for payment
- **flutter_inappwebview: ^6.1.5** - In-app WebView

### Utilities:
- **dartz: ^0.10.1** - Functional programming
- **fluttertoast: ^8.2.8** - Toast messages
- **intl: ^0.19.0** - Internationalization
- **timeago: ^3.7.0** - Time formatting

---

## 🔌 API Integration

### Base URL Configuration
**File:** `lib/data/data_provider/remote_url.dart`

```dart
static const String rootUrl = "https://carbaz.mamunuiux.com/";
static const String baseUrl = '${rootUrl}api/';
```

### API Endpoints yang Digunakan:

#### Authentication:
- `POST /api/store-login` - Login
- `POST /api/store-register` - Register
- `POST /api/user-verification` - Verify OTP
- `POST /api/send-forget-password` - Forgot password
- `POST /api/store-reset-password` - Reset password

#### Car Management:
- `GET /api/listings` - Get all cars
- `GET /api/listing/{id}` - Get car details
- `POST /api/user/car` - Create car
- `PUT /api/user/car/{id}` - Update car
- `DELETE /api/user/car/{id}` - Delete car

#### User Features:
- `GET /api/user/dashboard` - User dashboard
- `GET /api/user/wishlists` - Wishlist
- `GET /api/user/booking-history` - Booking history
- `GET /api/user/reviews` - Reviews
- `PUT /api/user/update-profile` - Update profile

#### Payment:
- `GET /api/user/pricing-plan` - Subscription plans
- `POST /api/user/pay-via-stripe/{id}` - Stripe payment
- `POST /api/user/pay-via-bank/{id}` - Bank payment
- WebView untuk: Razorpay, PayPal, Flutterwave, Paystack, Mollie, Instamojo

---

## 🎨 Fitur Aplikasi

### 1. Authentication
- ✅ Login
- ✅ Registration
- ✅ OTP Verification
- ✅ Forgot Password
- ✅ Reset Password

### 2. Home & Discovery
- ✅ Home screen dengan banner
- ✅ Popular cars
- ✅ Top dealers
- ✅ Brand listing
- ✅ Search & Filter

### 3. Car Management
- ✅ View all cars
- ✅ Car details
- ✅ Add car (multi-step form)
- ✅ Edit car
- ✅ Delete car
- ✅ Compare cars

### 4. User Features
- ✅ Profile management
- ✅ Wishlist
- ✅ Booking history
- ✅ Reviews
- ✅ KYC verification

### 5. Dealer Features
- ✅ Dealer profile
- ✅ Manage cars
- ✅ Subscription plans
- ✅ Withdraw requests

### 6. Payment
- ✅ Multiple payment gateways
- ✅ Subscription payment
- ✅ Transaction history

---

## ⚙️ Yang Perlu Di-Customize

### 1. **API Base URL** ⚠️ PENTING

**File:** `lib/data/data_provider/remote_url.dart`

```dart
// Ganti dengan URL backend Anda
static const String rootUrl = "https://your-domain.com/";
static const String baseUrl = '${rootUrl}api/';
```

**Saat ini menggunakan:**
```dart
static const String rootUrl = "https://carbaz.mamunuiux.com/";
```

### 2. **App Name & Package Name**

**File:** `pubspec.yaml`
```yaml
name: carsbnb  # Ganti dengan nama app Anda
```

**File:** `android/app/build.gradle`
```gradle
applicationId "com.example.carsbnb"  # Ganti dengan package name Anda
```

**File:** `ios/Runner/Info.plist`
- Update `CFBundleName` dan `CFBundleDisplayName`

### 3. **App Icon & Splash Screen**

**File:** `pubspec.yaml`
```yaml
flutter_launcher_icons:
  android: "launcher_icon"
  ios: true
  image_path: "assets/luncher_icon.png"  # Ganti dengan icon Anda
```

**Splash Screen:**
- Update images di `assets/images/splash_*.jpg`
- Atau update di `android/app/src/main/res/` dan `ios/Runner/Assets.xcassets/`

### 4. **App Colors & Theme**

**File:** `lib/widgets/custom_theme.dart`
- Update warna primary, secondary, dll sesuai brand Anda

### 5. **Strings & Translations**

**File:** `lib/utils/k_string.dart`
- Update app name, default strings

**File:** `lib/utils/language_string.dart`
- Update translations jika ada

### 6. **API Endpoints (Jika Ada Perubahan)**

**File:** `lib/data/data_provider/remote_url.dart`
- Update endpoints jika backend API berbeda
- Tambah endpoints baru jika diperlukan

### 7. **Payment Gateways**

Jika menggunakan payment gateway yang berbeda:
- Update di `lib/presentation/screen/payment/`
- Update di `lib/data/data_provider/remote_url.dart`

### 8. **Firebase (Jika Diperlukan)**

Jika perlu push notification atau analytics:
- Setup Firebase project
- Tambah `google-services.json` (Android)
- Tambah `GoogleService-Info.plist` (iOS)
- Update `pubspec.yaml` dengan Firebase dependencies

---

## 🚀 Langkah-Langkah Customize

### Step 1: Setup Environment

```bash
# Install Flutter (jika belum)
# Download dari: https://flutter.dev/docs/get-started/install

# Install FVM (Flutter Version Manager) - Optional
fvm install stable
fvm use stable

# Install dependencies
cd mobile/main_files/source_code/CarBaz_flutter-main
flutter pub get
```

### Step 2: Update API Base URL

1. Buka `lib/data/data_provider/remote_url.dart`
2. Ganti `rootUrl` dengan URL backend Anda:
```dart
static const String rootUrl = "https://your-backend-domain.com/";
```

### Step 3: Update App Identity

1. **App Name:**
   - `pubspec.yaml` → `name: your_app_name`
   - `android/app/build.gradle` → `applicationId`
   - `ios/Runner/Info.plist` → Bundle identifiers

2. **App Icon:**
   - Ganti `assets/luncher_icon.png`
   - Run: `flutter pub run flutter_launcher_icons`

3. **Splash Screen:**
   - Update images di `assets/images/`
   - Atau customize native splash screen

### Step 4: Update Theme & Colors

1. Buka `lib/widgets/custom_theme.dart`
2. Update warna sesuai brand:
```dart
static const Color primaryColor = Color(0xFFYourColor);
static const Color secondaryColor = Color(0xFFYourColor);
```

### Step 5: Test API Connection

1. Pastikan backend API sudah running
2. Test dengan endpoint `/api/website-setup`
3. Check response di app

### Step 6: Build & Run

```bash
# Android
flutter build apk --release
# atau
flutter build appbundle --release

# iOS
flutter build ios --release
```

---

## 📱 Platform Support

- ✅ **Android** - Fully supported
- ✅ **iOS** - Fully supported
- ⚪ **Web** - Basic support
- ⚪ **Windows** - Basic support
- ⚪ **Linux** - Basic support
- ⚪ **macOS** - Basic support

---

## 🔧 Konfigurasi Tambahan yang Mungkin Diperlukan

### 1. **Android Configuration**

**File:** `android/app/build.gradle`
- Update `minSdkVersion` (saat ini: 21)
- Update `targetSdkVersion`
- Update `versionCode` dan `versionName`

**File:** `android/app/src/main/AndroidManifest.xml`
- Update permissions jika diperlukan
- Update package name

### 2. **iOS Configuration**

**File:** `ios/Runner/Info.plist`
- Update `CFBundleIdentifier`
- Update `CFBundleName`
- Tambah permissions jika diperlukan (Camera, Location, dll)

**File:** `ios/Podfile`
- Update iOS deployment target jika diperlukan

### 3. **Environment Variables**

Jika perlu environment variables (dev, staging, production):
- Install `flutter_dotenv` package
- Buat file `.env`, `.env.dev`, `.env.prod`
- Load di `main.dart`

### 4. **API Authentication**

**File:** `lib/data/data_provider/remote_data_source.dart`
- Check bagaimana token disimpan (kemungkinan di SharedPreferences)
- Pastikan token dikirim di header setiap request

### 5. **Error Handling**

**File:** `lib/presentation/errors/`
- Customize error messages sesuai kebutuhan
- Update error handling di repository

---

## 🆕 Fitur Baru yang Perlu Ditambahkan (Berdasarkan Backend)

### 1. **Mediator Registration & Features**

**File baru yang perlu dibuat:**
- `lib/presentation/screen/authentication/mediator_registration_screen.dart`
- `lib/data/model/auth/mediator_register_model.dart`
- `lib/logic/cubit/mediator/mediator_cubit.dart`
- Update `lib/data/data_provider/remote_url.dart`:
  ```dart
  static const String mediatorRegister = '${baseUrl}mediator/store-register';
  ```

### 2. **Showroom Barcode Scanner**

**File baru:**
- `lib/presentation/screen/showroom/scan_barcode_screen.dart`
- Install package: `qr_code_scanner` atau `mobile_scanner`
- Update `remote_url.dart`:
  ```dart
  static String scanShowroom(String code) => '${baseUrl}scan-showroom/$code';
  ```

### 3. **Leasing Application Flow**

**File baru:**
- `lib/presentation/screen/leasing/application_screen.dart`
- `lib/presentation/screen/leasing/payment_capability_calculator.dart`
- `lib/data/model/leasing/application_model.dart`
- `lib/logic/cubit/leasing/leasing_cubit.dart`

### 4. **Calculator Payment Capability**

**File baru:**
- `lib/presentation/screen/calculator/payment_capability_screen.dart`
- Update `remote_url.dart`:
  ```dart
  static const String calculatePaymentCapability = '${baseUrl}calculate-payment-capability';
  ```

---

## 📋 Checklist Customization

### Basic Setup:
- [ ] Update API base URL
- [ ] Update app name & package name
- [ ] Update app icon
- [ ] Update splash screen
- [ ] Update theme colors
- [ ] Test API connection

### Advanced Setup:
- [ ] Setup Firebase (jika diperlukan)
- [ ] Update payment gateways
- [ ] Add new features (Mediator, Leasing, dll)
- [ ] Update error handling
- [ ] Add analytics (jika diperlukan)
- [ ] Setup push notifications

### Build & Deploy:
- [ ] Test di Android device/emulator
- [ ] Test di iOS device/simulator
- [ ] Build release APK/AAB
- [ ] Build release IPA
- [ ] Upload ke Play Store
- [ ] Upload ke App Store

---

## 🐛 Troubleshooting

### 1. **API Connection Error**
- Check base URL di `remote_url.dart`
- Check network permissions di AndroidManifest.xml
- Check CORS settings di backend

### 2. **Build Error**
- Run `flutter clean`
- Run `flutter pub get`
- Check Flutter version compatibility

### 3. **iOS Build Error**
- Run `cd ios && pod install`
- Check Xcode version
- Check iOS deployment target

### 4. **Dependencies Error**
- Run `flutter pub upgrade`
- Check `pubspec.yaml` untuk version conflicts
- Delete `pubspec.lock` dan run `flutter pub get` lagi

---

## 📚 Resources

- **Flutter Documentation:** https://flutter.dev/docs
- **BLoC Pattern:** https://bloclibrary.dev/
- **API Documentation:** Lihat `DOKUMENTASI_REGISTRASI.md` dan `DOKUMENTASI_ROLES_DAN_INTEGRASI.md`

---

## ⚠️ Catatan Penting

1. **API Base URL** harus di-update sebelum build
2. **Package Name** harus unique untuk Play Store/App Store
3. **App Icon** harus sesuai dengan guidelines Play Store/App Store
4. **Permissions** harus di-declare di AndroidManifest.xml dan Info.plist
5. **Backend API** harus compatible dengan endpoints yang digunakan di app

---

**Dokumentasi ini mencakup struktur lengkap Flutter app dan langkah-langkah untuk customize sesuai kebutuhan.**




