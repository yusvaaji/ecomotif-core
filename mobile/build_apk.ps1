# Script untuk Build APK ECOMOTIF
# Pastikan Android SDK sudah terinstall sebelum menjalankan script ini

param(
    [string]$OutputDir = "C:\Grafik\UMAR\ecomotif\mainv2\main_files\mobile"
)

Write-Host "========================================"
Write-Host "  Build APK ECOMOTIF"
Write-Host "========================================"
Write-Host ""

# Check Flutter
Write-Host "Checking Flutter installation..."
$flutterPath = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
$env:PATH = $flutterPath

try {
    $flutterVersion = flutter --version 2>&1 | Select-Object -First 1
    Write-Host "✓ Flutter found: $flutterVersion"
} catch {
    Write-Host "✗ Flutter not found. Please install Flutter first."
    exit 1
}

# Check Android SDK
Write-Host ""
Write-Host "Checking Android SDK..."
$androidHome = [System.Environment]::GetEnvironmentVariable("ANDROID_HOME", "User")
if (-not $androidHome) {
    $androidHome = [System.Environment]::GetEnvironmentVariable("ANDROID_HOME", "Machine")
}
if (-not $androidHome) {
    $androidHome = "$env:LOCALAPPDATA\Android\Sdk"
}

if (Test-Path $androidHome) {
    Write-Host "✓ Android SDK found at: $androidHome"
    $env:ANDROID_HOME = $androidHome
    $env:ANDROID_SDK_ROOT = $androidHome
} else {
    Write-Host "✗ Android SDK not found at: $androidHome"
    Write-Host ""
    Write-Host "Please install Android SDK first:"
    Write-Host "1. Install Android Studio from: https://developer.android.com/studio"
    Write-Host "2. Or see INSTALL_ANDROID_SDK.md for detailed instructions"
    exit 1
}

# Navigate to project
$projectPath = "main_files\source_code\CarBaz_flutter-main"
if (-not (Test-Path $projectPath)) {
    Write-Host "✗ Project not found at: $projectPath"
    exit 1
}

Set-Location $projectPath
Write-Host ""
Write-Host "Project directory: $(Get-Location)"

# Clean previous build
Write-Host ""
Write-Host "Cleaning previous build..."
flutter clean | Out-Null

# Get dependencies
Write-Host ""
Write-Host "Getting dependencies..."
flutter pub get

# Build APK
Write-Host ""
Write-Host "Building APK (Release)..."
Write-Host "This may take several minutes..."
flutter build apk --release

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✓ Build successful!"
    
    # Copy APK to output directory
    $apkPath = "build\app\outputs\flutter-apk\app-release.apk"
    if (Test-Path $apkPath) {
        $outputPath = Join-Path $OutputDir "ECOMOTIF-release.apk"
        New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
        Copy-Item $apkPath -Destination $outputPath -Force
        Write-Host ""
        Write-Host "✓ APK copied to: $outputPath"
        Write-Host ""
        Write-Host "========================================"
        Write-Host "  Build Complete!"
        Write-Host "========================================"
        Write-Host "APK Location: $outputPath"
        Write-Host ""
    } else {
        Write-Host "✗ APK file not found at: $apkPath"
    }
} else {
    Write-Host ""
    Write-Host "✗ Build failed. Please check the errors above."
    exit 1
}




