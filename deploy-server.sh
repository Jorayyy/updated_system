#!/bin/bash

################################################################################
# MEBS HR System - Hostinger Server Deployment Script
# 
# Usage: bash deploy-server.sh
# 
# This script performs all necessary deployment steps on the Hostinger server
################################################################################

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
APP_NAME="MEBS HR System"
TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}$APP_NAME - Server Deployment${NC}"
echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}Timestamp: $TIMESTAMP${NC}"
echo ""

# Function to print section headers
print_section() {
    echo -e "${YELLOW}$1${NC}"
    echo "---"
}

# Function to print success messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Function to print error messages
print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Trap errors
set -o errexit
trap 'print_error "Script failed at line $LINENO"' ERR

# Step 1: Navigate to app directory
print_section "Step 1: Setting up directories"
cd "$APP_DIR" || { print_error "Failed to navigate to app directory"; exit 1; }
pwd
print_success "In correct directory"

# Step 2: Install PHP dependencies
print_section "Step 2: Installing PHP dependencies"
if [ -f "composer.json" ]; then
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction
        print_success "PHP dependencies installed"
    else
        print_error "Composer not found. Please install Composer first."
        exit 1
    fi
else
    print_error "composer.json not found"
    exit 1
fi

# Step 3: Install Node dependencies and build assets
print_section "Step 3: Building frontend assets"
if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        npm install --legacy-peer-deps --production
        npm run build
        print_success "Frontend assets built successfully"
    else
        print_error "npm not found. Please install Node.js first."
        exit 1
    fi
else
    print_error "package.json not found"
    exit 1
fi

# Step 4: Verify .env file
print_section "Step 4: Verifying environment configuration"
if [ ! -f ".env" ]; then
    print_error ".env file not found"
    print_error "Please upload .env.production and rename it to .env"
    exit 1
fi
print_success ".env file exists"

# Step 5: Generate application key (if needed)
print_section "Step 5: Setting up application key"
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
    print_success "Application key generated"
else
    print_success "Application key already set"
fi

# Step 6: Clear caches
print_section "Step 6: Clearing application caches"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Step 7: Storage and bootstrap permissions
print_section "Step 7: Setting directory permissions"
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod 644 storage/logs/*
print_success "Permissions set correctly"

# Step 8: Database migrations
print_section "Step 8: Running database migrations"
php artisan migrate --force
print_success "Database migrations completed"

# Step 9: Cache optimization
print_section "Step 9: Optimizing application"
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Step 10: Symlink storage (if needed)
print_section "Step 10: Setting up storage symlink"
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    print_success "Storage symlink created"
else
    print_success "Storage symlink already exists"
fi

# Step 11: Seed database (optional - uncomment if needed)
# print_section "Step 11: Seeding database"
# php artisan db:seed --force
# print_success "Database seeded"

# Step 12: Final verification
print_section "Step 12: Deployment Verification"
echo "Checking critical files..."
if [ -f "artisan" ]; then
    print_success "✓ artisan file exists"
fi
if [ -f ".env" ]; then
    print_success "✓ .env file exists"
fi
if [ -d "storage" ]; then
    print_success "✓ storage directory exists"
fi
if [ -d "public" ]; then
    print_success "✓ public directory exists"
fi
if [ -d "vendor" ]; then
    print_success "✓ vendor directory exists"
fi

echo ""
echo -e "${BLUE}================================${NC}"
echo -e "${GREEN}Deployment Completed Successfully!${NC}"
echo -e "${BLUE}================================${NC}"
echo ""
echo -e "${YELLOW}Post-Deployment Checklist:${NC}"
echo "  ☐ Verify .env file is set to production"
echo "  ☐ Set public_html/.../public as public root in Hostinger"
echo "  ☐ Enable HTTPS/SSL certificate"
echo "  ☐ Test application at https://yourdomain.com"
echo "  ☐ Set up cron job for scheduled tasks:"
echo "    * * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo -e "${YELLOW}Troubleshooting:${NC}"
echo "  - Check storage/logs/laravel.log for errors"
echo "  - Verify database connection in .env"
echo "  - Ensure public directory permissions are correct"
echo ""
echo -e "${GREEN}Deployment script completed at: $(date)${NC}"
