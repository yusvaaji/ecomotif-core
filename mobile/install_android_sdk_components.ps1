# Script untuk Install Android SDK Components
# Jalankan script ini sebagai Administrator untuk install komponen SDK yang diperlukan

param(
    [string]$SdkPath = "$env:LOCALAPPDATA\Android\Sdk"
)

Write-Host "========================================"
Write-Host "  Install Android SDK Components"
Write-Host "========================================"
Write-Host ""

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "Warning: Not running as Administrator. Some operations may fail." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "SDK Path: $SdkPath"
Write-Host ""

# Create SDK directory if not exists
if (-not (Test-Path $SdkPath)) {
    Write-Host "Creating SDK directory..."
    New-Item -ItemType Directory -Force -Path $SdkPath | Out-Null
}

# Download Command Line Tools if not exists
$toolsPath = "$SdkPath\cmdline-tools\latest\bin"
$sdkmanager = "$toolsPath\sdkmanager.bat"

if (-not (Test-Path $sdkmanager)) {
    Write-Host "Command Line Tools not found. Downloading..."
    Write-Host "This may take a few minutes..."
    
    $zipPath = "$env:TEMP\android-cmdline-tools.zip"
    $toolsUrl = "https://dl.google.com/android/repository/commandlinetools-win-11076708_latest.zip"
    
    try {
        Write-Host "Downloading from: $toolsUrl"
        Invoke-WebRequest -Uri $toolsUrl -OutFile $zipPath -UseBasicParsing -ErrorAction Stop
        
        Write-Host "Extracting..."
        $extractPath = "$env:TEMP\android-cmdline-tools"
        if (Test-Path $extractPath) {
            Remove-Item $extractPath -Recurse -Force
        }
        Expand-Archive -Path $zipPath -DestinationPath $extractPath -Force
        
        # Move to correct location
        $cmdlineToolsDir = "$SdkPath\cmdline-tools"
        New-Item -ItemType Directory -Force -Path $cmdlineToolsDir | Out-Null
        
        if (Test-Path "$extractPath\cmdline-tools") {
            Move-Item "$extractPath\cmdline-tools\*" "$cmdlineToolsDir\latest\" -Force
        } else {
            # If structure is different
            Get-ChildItem $extractPath | Move-Item -Destination "$cmdlineToolsDir\latest\" -Force
        }
        
        Write-Host "[OK] Command Line Tools installed"
        Remove-Item $zipPath -Force -ErrorAction SilentlyContinue
        Remove-Item $extractPath -Recurse -Force -ErrorAction SilentlyContinue
    } catch {
        Write-Host "[ERROR] Error downloading Command Line Tools: $_" -ForegroundColor Red
        Write-Host ""
        Write-Host "Please download manually from:" -ForegroundColor Yellow
        Write-Host "https://developer.android.com/studio#command-tools" -ForegroundColor Yellow
        Write-Host "Extract to: $SdkPath\cmdline-tools\latest\" -ForegroundColor Yellow
        exit 1
    }
}

# Verify sdkmanager exists
if (-not (Test-Path $sdkmanager)) {
    Write-Host "[ERROR] sdkmanager still not found at: $sdkmanager" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Setting environment variables..."
$env:ANDROID_HOME = $SdkPath
$env:ANDROID_SDK_ROOT = $SdkPath
[System.Environment]::SetEnvironmentVariable("ANDROID_HOME", $SdkPath, "User")
[System.Environment]::SetEnvironmentVariable("ANDROID_SDK_ROOT", $SdkPath, "User")

Write-Host ""
Write-Host "Accepting Android licenses..."
Write-Host "Press 'y' when prompted..."
try {
    $licenses = echo "y" | & $sdkmanager --sdk_root=$SdkPath --licenses 2>&1
    Write-Host $licenses
} catch {
    Write-Host "Note: License acceptance may require manual confirmation" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Installing required SDK components..."
Write-Host "This will download several GB of data and may take 10-30 minutes..."
Write-Host ""

$components = @(
    "platform-tools",
    "platforms;android-34",
    "build-tools;34.0.0"
)

foreach ($component in $components) {
    Write-Host "Installing: $component"
    try {
        & $sdkmanager --sdk_root=$SdkPath $component --no_https 2>&1 | ForEach-Object {
            Write-Host $_
        }
        Write-Host "[OK] $component installed" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Error installing $component : $_" -ForegroundColor Red
    }
    Write-Host ""
}

# Verify installation
Write-Host ""
Write-Host "Verifying installation..."
$checks = @{
    "platform-tools\adb.exe" = "Platform Tools"
    "build-tools\34.0.0\aapt.exe" = "Build Tools 34.0.0"
    "platforms\android-34\android.jar" = "Android Platform 34"
}

$allOk = $true
foreach ($check in $checks.GetEnumerator()) {
    $filePath = Join-Path $SdkPath $check.Key
    if (Test-Path $filePath) {
        Write-Host "[OK] $($check.Value) - OK" -ForegroundColor Green
    } else {
        Write-Host "[MISSING] $($check.Value) - Missing" -ForegroundColor Red
        $allOk = $false
    }
}

if ($allOk) {
    Write-Host ""
    Write-Host "========================================"
    Write-Host "  Installation Complete!"
    Write-Host "========================================"
    Write-Host ""
    Write-Host "Please restart your terminal/Cursor and run:"
    Write-Host "  flutter doctor" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "Some components are missing. Please run this script again." -ForegroundColor Yellow
    Write-Host "Or install Android Studio for automatic setup:" -ForegroundColor Yellow
    Write-Host "https://developer.android.com/studio" -ForegroundColor Cyan
}

