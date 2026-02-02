# MEBS HR System - Local Deployment Preparation Script
# Run this script locally before deploying to Hostinger

Write-Host "================================" -ForegroundColor Cyan
Write-Host "MEBS HR System - Local Deploy Setup" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check if Git is initialized
if (!(Test-Path .git)) {
    Write-Host "Initializing Git repository..." -ForegroundColor Yellow
    git init
    git add .
    git commit -m "Initial MEBS HR System commit"
} else {
    Write-Host "Git repository already initialized" -ForegroundColor Green
}

Write-Host ""
Write-Host "Step 1: Installing PHP dependencies..." -ForegroundColor Yellow
composer install

Write-Host ""
Write-Host "Step 2: Installing Node.js dependencies..." -ForegroundColor Yellow
npm install

Write-Host ""
Write-Host "Step 3: Building frontend assets..." -ForegroundColor Yellow
npm run build

Write-Host ""
Write-Host "Step 4: Generating application key..." -ForegroundColor Yellow
php artisan key:generate

Write-Host ""
Write-Host "Step 5: Clearing caches..." -ForegroundColor Yellow
php artisan cache:clear
php artisan config:clear
php artisan view:clear

Write-Host ""
Write-Host "Step 6: Running database migrations (local)..." -ForegroundColor Yellow
php artisan migrate

Write-Host ""
Write-Host "Step 7: Creating .env.production file..." -ForegroundColor Yellow

$envProduction = @"
APP_NAME="MEBS HR System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
"@

Set-Content -Path ".env.production" -Value $envProduction
Write-Host ".env.production file created" -ForegroundColor Green

Write-Host ""
Write-Host "Step 8: Creating production-ready archive..." -ForegroundColor Yellow

# Create deployment archive (excluding unnecessary files)
$excludeFiles = @(
    '.env',
    '.env.example',
    '.git',
    '.gitignore',
    'node_modules',
    'storage/logs/*',
    'storage/framework/cache/*',
    'storage/framework/sessions/*',
    'bootstrap/cache/*',
    'tests',
    '*.md',
    '.editorconfig',
    '.env.production'
)

Write-Host "Creating deployment package..." -ForegroundColor Yellow

# Create a clean copy for deployment
$deployDir = "mebs-hiyas-deployment"
if (Test-Path $deployDir) {
    Remove-Item -Path $deployDir -Recurse -Force
}

Copy-Item -Path "." -Destination $deployDir -Recurse -Exclude $excludeFiles

Write-Host "Deployment package created at: $deployDir" -ForegroundColor Green

Write-Host ""
Write-Host "================================" -ForegroundColor Green
Write-Host "Deployment Preparation Complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Update .env.production with your Hostinger details" -ForegroundColor White
Write-Host "2. Upload the '$deployDir' folder to Hostinger via SFTP" -ForegroundColor White
Write-Host "3. SSH into Hostinger and run: bash deploy-server.sh" -ForegroundColor White
Write-Host ""
Write-Host "Upload Instructions:" -ForegroundColor Yellow
Write-Host "  - Use FileZilla, WinSCP, or your preferred SFTP client" -ForegroundColor Gray
Write-Host "  - Upload to: public_html/mebs-hiyas/" -ForegroundColor Gray
Write-Host "  - Copy .env.production to server and rename to .env" -ForegroundColor Gray
Write-Host ""
