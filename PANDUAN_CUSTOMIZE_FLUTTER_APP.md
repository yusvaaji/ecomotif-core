# Panduan Customize Flutter App - Step by Step

## 🎯 Overview

Panduan lengkap untuk customize Flutter app CarBaz agar sesuai dengan backend yang sudah di-update (termasuk fitur Mediator, Leasing, Calculator, dll).

---

## 📋 Prerequisites

Sebelum mulai customize, pastikan:
- ✅ Flutter SDK terinstall (versi sesuai `.fvmrc` atau Flutter stable)
- ✅ Android Studio / VS Code dengan Flutter extension
- ✅ Backend API sudah running dan accessible
- ✅ Git (untuk version control)

---

## 🔧 Step 1: Setup Project

### 1.1 Install Dependencies

```bash
cd mobile/main_files/source_code/CarBaz_flutter-main
flutter pub get
```

### 1.2 Check Flutter Version

```bash
flutter --version
# Pastikan sesuai dengan yang di .fvmrc (stable)
```

### 1.3 Run App (Test)

```bash
# Android
flutter run

# iOS (jika di Mac)
flutter run -d ios
```

---

## 🔗 Step 2: Update API Configuration

### 2.1 Update Base URL

**File:** `lib/data/data_provider/remote_url.dart`

**Ganti:**
```dart
static const String rootUrl = "https://carbaz.mamunuiux.com/";
```

**Menjadi:**
```dart
static const String rootUrl = "https://your-backend-domain.com/";
// atau untuk development:
// static const String rootUrl = "http://192.168.1.100/";
```

### 2.2 Tambah Endpoints Baru

**File:** `lib/data/data_provider/remote_url.dart`

**Tambah di class `RemoteUrls`:**

```dart
// Mediator Registration
static const String mediatorRegister = '${baseUrl}mediator/store-register';

// Showroom Barcode
static String scanShowroom(String code) => '${baseUrl}scan-showroom/$code';
static const String showroomGenerateBarcode = '${baseUrl}user/showroom/generate-barcode';
static const String showroomGetBarcode = '${baseUrl}user/showroom/barcode';

// Calculator
static const String calculatePaymentCapability = '${baseUrl}calculate-payment-capability';
static const String calculateInstallment = '${baseUrl}calculate-installment';

// Leasing Application
static const String submitApplication = '${baseUrl}applications';
static String applicationStatus(String id) => '${baseUrl}applications/$id';
static String uploadDocuments(String id) => '${baseUrl}applications/$id/documents';
static String payDP(String id) => '${baseUrl}applications/$id/pay-dp';

// Mediator Dashboard
static const String mediatorDashboard = '${baseUrl}user/mediator/dashboard';
static const String mediatorApplications = '${baseUrl}user/mediator/applications';
static const String mediatorCreateApplication = '${baseUrl}user/mediator/applications';

// Showroom Applications
static const String showroomApplications = '${baseUrl}user/showroom/applications';
static String showroomApplicationDetails(String id) => '${baseUrl}user/showroom/applications/$id';
static String poolToLeasing(String id) => '${baseUrl}user/showroom/applications/$id/pool-to-leasing';
static String appealToLeasing(String id) => '${baseUrl}user/showroom/applications/$id/appeal';

// Marketing
static const String marketingDashboard = '${baseUrl}user/marketing/dashboard';
static const String marketingApplications = '${baseUrl}user/marketing/applications';
static const String marketingCreateApplication = '${baseUrl}user/marketing/applications';
```

---

## 🆕 Step 3: Implementasi Fitur Baru

### 3.1 Mediator Registration

#### A. Buat Model

**File:** `lib/data/model/auth/mediator_register_model.dart`

```dart
class MediatorRegisterModel {
  final String name;
  final String email;
  final String password;
  final String passwordConfirmation;
  final String phone;
  final String address;
  final int? showroomId;

  MediatorRegisterModel({
    required this.name,
    required this.email,
    required this.password,
    required this.passwordConfirmation,
    required this.phone,
    required this.address,
    this.showroomId,
  });

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'phone': phone,
      'address': address,
      if (showroomId != null) 'showroom_id': showroomId,
    };
  }
}
```

#### B. Buat Screen

**File:** `lib/presentation/screen/authentication/mediator_registration_screen.dart`

```dart
// Copy dari registration_screen.dart
// Update form fields untuk include phone, address, showroom_id
// Update API call ke mediatorRegister endpoint
```

#### C. Update Route

**File:** `lib/routes/route_names.dart`

```dart
static const String mediatorRegistrationScreen = '/mediatorRegistrationScreen';

// Di generateRoutes:
case RouteNames.mediatorRegistrationScreen:
  return MaterialPageRoute(
    settings: settings,
    builder: (_) => const MediatorRegistrationScreen(),
  );
```

### 3.2 Payment Capability Calculator

#### A. Buat Model

**File:** `lib/data/model/calculator/payment_capability_model.dart`

```dart
class PaymentCapabilityRequest {
  final double monthlyIncome;
  final double monthlyExpenses;
  final double existingLoans;
  final double carPrice;
  final int? tenureMonths;

  PaymentCapabilityRequest({
    required this.monthlyIncome,
    required this.monthlyExpenses,
    required this.existingLoans,
    required this.carPrice,
    this.tenureMonths,
  });

  Map<String, dynamic> toJson() {
    return {
      'monthly_income': monthlyIncome,
      'monthly_expenses': monthlyExpenses,
      'existing_loans': existingLoans,
      'car_price': carPrice,
      if (tenureMonths != null) 'tenure_months': tenureMonths,
    };
  }
}

class PaymentCapabilityResponse {
  final bool affordable;
  final double disposableIncome;
  final double maxMonthlyPayment;
  final double recommendedDp;
  final double recommendedDpPercentage;
  final double loanAmount;
  final double monthlyInstallment;
  final int tenureMonths;

  PaymentCapabilityResponse({
    required this.affordable,
    required this.disposableIncome,
    required this.maxMonthlyPayment,
    required this.recommendedDp,
    required this.recommendedDpPercentage,
    required this.loanAmount,
    required this.monthlyInstallment,
    required this.tenureMonths,
  });

  factory PaymentCapabilityResponse.fromJson(Map<String, dynamic> json) {
    return PaymentCapabilityResponse(
      affordable: json['affordable'] ?? false,
      disposableIncome: (json['disposable_income'] ?? 0).toDouble(),
      maxMonthlyPayment: (json['max_monthly_payment'] ?? 0).toDouble(),
      recommendedDp: (json['recommended_dp'] ?? 0).toDouble(),
      recommendedDpPercentage: (json['recommended_dp_percentage'] ?? 0).toDouble(),
      loanAmount: (json['loan_amount'] ?? 0).toDouble(),
      monthlyInstallment: (json['monthly_installment'] ?? 0).toDouble(),
      tenureMonths: json['tenure_months'] ?? 36,
    );
  }
}
```

#### B. Buat Repository

**File:** `lib/logic/repository/calculator_repository.dart`

```dart
abstract class CalculatorRepository {
  Future<Either<Failure, PaymentCapabilityResponse>> calculatePaymentCapability(
    PaymentCapabilityRequest request,
  );
}

class CalculatorRepositoryImpl implements CalculatorRepository {
  final RemoteDataSources remoteDataSource;

  CalculatorRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Either<Failure, PaymentCapabilityResponse>> calculatePaymentCapability(
    PaymentCapabilityRequest request,
  ) async {
    try {
      final response = await remoteDataSource.post(
        RemoteUrls.calculatePaymentCapability,
        request.toJson(),
      );
      return Right(PaymentCapabilityResponse.fromJson(response['data']));
    } catch (e) {
      return Left(ServerFailure(e.toString()));
    }
  }
}
```

#### C. Buat Cubit

**File:** `lib/logic/cubit/calculator/calculator_cubit.dart`

```dart
class CalculatorCubit extends Cubit<CalculatorState> {
  final CalculatorRepository repository;

  CalculatorCubit({required this.repository}) : super(CalculatorInitial());

  Future<void> calculatePaymentCapability(PaymentCapabilityRequest request) async {
    emit(CalculatorLoading());
    final result = await repository.calculatePaymentCapability(request);
    result.fold(
      (failure) => emit(CalculatorError(failure.message)),
      (response) => emit(CalculatorLoaded(response)),
    );
  }
}
```

#### D. Buat Screen

**File:** `lib/presentation/screen/calculator/payment_capability_screen.dart`

```dart
// Form dengan fields:
// - Monthly Income
// - Monthly Expenses
// - Existing Loans
// - Car Price
// - Tenure Months (optional)
// 
// Button "Calculate"
// Display results setelah calculate
```

### 3.3 Barcode Scanner untuk Showroom

#### A. Install Package

**File:** `pubspec.yaml`

```yaml
dependencies:
  mobile_scanner: ^3.5.0
  # atau
  qr_code_scanner: ^1.0.1
```

#### B. Buat Screen

**File:** `lib/presentation/screen/showroom/scan_barcode_screen.dart`

```dart
import 'package:mobile_scanner/mobile_scanner.dart';

class ScanBarcodeScreen extends StatefulWidget {
  @override
  _ScanBarcodeScreenState createState() => _ScanBarcodeScreenState();
}

class _ScanBarcodeScreenState extends State<ScanBarcodeScreen> {
  final MobileScannerController controller = MobileScannerController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Scan Showroom Barcode')),
      body: MobileScanner(
        controller: controller,
        onDetect: (capture) {
          final List<Barcode> barcodes = capture.barcodes;
          for (final barcode in barcodes) {
            if (barcode.rawValue != null) {
              // Call API untuk scan barcode
              _scanBarcode(barcode.rawValue!);
            }
          }
        },
      ),
    );
  }

  Future<void> _scanBarcode(String code) async {
    // Call API: RemoteUrls.scanShowroom(code)
    // Navigate ke showroom details atau application form
  }
}
```

### 3.4 Leasing Application Flow

#### A. Buat Model

**File:** `lib/data/model/leasing/application_model.dart`

```dart
class LeasingApplicationModel {
  final int carId;
  final double downPayment;
  final double installmentAmount;
  final int? showroomId;
  final List<String>? documents;

  LeasingApplicationModel({
    required this.carId,
    required this.downPayment,
    required this.installmentAmount,
    this.showroomId,
    this.documents,
  });

  Map<String, dynamic> toJson() {
    return {
      'car_id': carId,
      'down_payment': downPayment,
      'installment_amount': installmentAmount,
      if (showroomId != null) 'showroom_id': showroomId,
    };
  }
}
```

#### B. Buat Repository & Cubit

Similar dengan calculator, buat:
- `lib/logic/repository/leasing_repository.dart`
- `lib/logic/cubit/leasing/leasing_cubit.dart`

#### C. Buat Screens

- `lib/presentation/screen/leasing/application_form_screen.dart`
- `lib/presentation/screen/leasing/application_status_screen.dart`
- `lib/presentation/screen/leasing/upload_documents_screen.dart`

---

## 🎨 Step 4: Update UI/UX

### 4.1 Update Theme Colors

**File:** `lib/widgets/custom_theme.dart`

```dart
class MyTheme {
  static ThemeData get theme => ThemeData(
    primaryColor: Color(0xFFYourPrimaryColor),
    colorScheme: ColorScheme.fromSeed(
      seedColor: Color(0xFFYourPrimaryColor),
    ),
    // ... other theme settings
  );
}
```

### 4.2 Update App Name

**File:** `lib/utils/k_string.dart`

```dart
class KString {
  static const String appName = "Your App Name";
  // ... other strings
}
```

### 4.3 Update App Icon

1. Ganti `assets/luncher_icon.png` dengan icon Anda
2. Run: `flutter pub run flutter_launcher_icons`

---

## 📱 Step 5: Update Native Configuration

### 5.1 Android

**File:** `android/app/build.gradle`

```gradle
android {
    defaultConfig {
        applicationId "com.yourcompany.yourapp"  // Ganti
        minSdkVersion 21
        targetSdkVersion 33
        versionCode 1
        versionName "1.0.0"
    }
}
```

**File:** `android/app/src/main/AndroidManifest.xml`

```xml
<manifest>
    <uses-permission android:name="android.permission.INTERNET"/>
    <uses-permission android:name="android.permission.CAMERA"/> <!-- Untuk barcode scanner -->
    
    <application
        android:label="Your App Name"  <!-- Ganti -->
        ...>
    </application>
</manifest>
```

### 5.2 iOS

**File:** `ios/Runner/Info.plist`

```xml
<key>CFBundleName</key>
<string>Your App Name</string>
<key>CFBundleDisplayName</key>
<string>Your App Name</string>
<key>CFBundleIdentifier</key>
<string>com.yourcompany.yourapp</string>

<!-- Permissions -->
<key>NSCameraUsageDescription</key>
<string>We need camera access to scan barcode</string>
```

---

## 🧪 Step 6: Testing

### 6.1 Test API Connection

```dart
// Test di app dengan check endpoint /api/website-setup
// Pastikan response OK
```

### 6.2 Test Fitur Baru

- [ ] Test Mediator Registration
- [ ] Test Calculator
- [ ] Test Barcode Scanner
- [ ] Test Leasing Application
- [ ] Test Showroom Features

### 6.3 Test di Device

```bash
# Android
flutter run -d <device-id>

# iOS
flutter run -d <device-id>
```

---

## 📦 Step 7: Build Release

### 7.1 Android

```bash
# APK
flutter build apk --release

# App Bundle (untuk Play Store)
flutter build appbundle --release
```

### 7.2 iOS

```bash
# Build untuk device
flutter build ios --release

# Archive di Xcode untuk App Store
```

---

## 📋 Checklist Lengkap

### Setup Awal:
- [ ] Install Flutter dependencies (`flutter pub get`)
- [ ] Update API base URL
- [ ] Test koneksi API
- [ ] Update app name & package name
- [ ] Update app icon
- [ ] Update theme colors

### Fitur Baru:
- [ ] Tambah endpoints baru di `remote_url.dart`
- [ ] Buat models untuk fitur baru
- [ ] Buat repositories untuk fitur baru
- [ ] Buat cubits untuk fitur baru
- [ ] Buat screens untuk fitur baru
- [ ] Update routes
- [ ] Test semua fitur baru

### Native Configuration:
- [ ] Update Android package name
- [ ] Update iOS bundle identifier
- [ ] Update permissions (Camera, dll)
- [ ] Update app name di native files

### Build & Deploy:
- [ ] Test di Android device
- [ ] Test di iOS device
- [ ] Build release APK/AAB
- [ ] Build release IPA
- [ ] Upload ke Play Store
- [ ] Upload ke App Store

---

## 🐛 Common Issues & Solutions

### Issue 1: API Connection Failed
**Solution:**
- Check base URL
- Check network permissions
- Check CORS di backend
- Test dengan Postman/curl

### Issue 2: Build Error
**Solution:**
```bash
flutter clean
flutter pub get
cd ios && pod install && cd ..
flutter run
```

### Issue 3: Package Not Found
**Solution:**
- Check `pubspec.yaml` dependencies
- Run `flutter pub get`
- Check internet connection

### Issue 4: iOS Build Failed
**Solution:**
```bash
cd ios
pod deintegrate
pod install
cd ..
flutter clean
flutter pub get
```

---

## 📚 Resources

- **Flutter Docs:** https://flutter.dev/docs
- **BLoC Pattern:** https://bloclibrary.dev/
- **API Docs:** Lihat `DOKUMENTASI_REGISTRASI.md`
- **Backend API:** Lihat `DOKUMENTASI_ROLES_DAN_INTEGRASI.md`

---

**Panduan ini mencakup semua langkah yang diperlukan untuk customize Flutter app sesuai dengan backend yang sudah di-update.**




