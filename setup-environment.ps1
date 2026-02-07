# MEBS HR System - Environment Setup & Initialization Script
# This script prepares a fresh Windows environment to run the MEBS Hiyas system

$ErrorActionPreference = "Stop"

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "   MEBS HR System - Global Environment Setup" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""

function Check-Command($cmd, $name) {
    if (Get-Command $cmd -ErrorAction SilentlyContinue) {
        Write-Host "✓ $name is installed" -ForegroundColor Green
        return $true
    } else {
        Write-Host "✗ $name is NOT installed. Please install it first." -ForegroundColor Red
        return $false
    }
}

# Step 1: Check Prerequisites
Write-Host "Step 1: Checking Prerequisites..." -ForegroundColor Yellow
$prereqs = $true
$prereqs = $prereqs -and (Check-Command "php" "PHP 8.1+")
$prereqs = $prereqs -and (Check-Command "composer" "Composer")
$prereqs = $prereqs -and (Check-Command "node" "Node.js & NPM")
$prereqs = $prereqs -and (Check-Command "mysql" "MySQL (Client/CLI)")

if (-not $prereqs) {
    Write-Host "`nError: Some prerequisites are missing. Please install XAMPP (or PHP+MySQL), Node.js, and Composer then try again." -ForegroundColor Red
    exit
}

# Step 2: Configure Environment
Write-Host "`nStep 2: Configuring .env file..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "✓ Created .env from .env.example" -ForegroundColor Green
        Write-Host "(!) ACTION REQUIRED: Open .env and update DB_DATABASE, DB_USERNAME, and DB_PASSWORD if necessary." -ForegroundColor Yellow
    } else {
        Write-Host "✗ .env.example not found!" -ForegroundColor Red
        exit
    }
} else {
    Write-Host "✓ .env file already exists" -ForegroundColor Green
}

# Step 3: Install Dependencies
Write-Host "`nStep 3: Installing PHP dependencies (Composer)..." -ForegroundColor Yellow
composer install

Write-Host "`nStep 4: Installing Node dependencies (NPM)..." -ForegroundColor Yellow
npm install

# Step 5: Build Assets
Write-Host "`nStep 5: Building frontend assets..." -ForegroundColor Yellow
npm run build

# Step 6: App Initialization
Write-Host "`nStep 6: Generating App Key..." -ForegroundColor Yellow
php artisan key:generate

# Step 7: Database Setup
Write-Host "`nStep 7: Setting up Database (Migrations & Seeders)..." -ForegroundColor Yellow
Write-Host "Note: This will create tables and populate them with the new site/account logic." -ForegroundColor White
$confirm = Read-Host "Do you want to run migrations and seed the database now? (y/n)"
if ($confirm -eq "y") {
    php artisan migrate:fresh --seed
    Write-Host "✓ Database initialized and seeded successfully." -ForegroundColor Green
} else {
    Write-Host "(!) Skipped database migration. Run 'php artisan migrate --seed' manually later." -ForegroundColor Cyan
}

# Final Message
Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "        SETUP COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To start the application, run:" -ForegroundColor White
Write-Host "  php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "Default Login:" -ForegroundColor White
Write-Host "  Email: admin@mebs.com" -ForegroundColor White
Write-Host "  Password: password" -ForegroundColor White
Write-Host ""
Write-Host "System is ready at http://127.0.0.1:8000" -ForegroundColor Cyan
