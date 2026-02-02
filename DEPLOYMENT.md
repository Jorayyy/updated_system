# MEBS HR System - Hostinger Deployment Guide

Complete step-by-step guide for deploying to Hostinger.

## Table of Contents
1. [Pre-Deployment](#pre-deployment)
2. [Local Preparation](#local-preparation)
3. [Hostinger Setup](#hostinger-setup)
4. [Server Deployment](#server-deployment)
5. [Post-Deployment](#post-deployment)
6. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment

### Requirements
- ✅ Hostinger Business Plan or higher
- ✅ Domain name (optional, can use Hostinger subdomain)
- ✅ SFTP/SSH access credentials
- ✅ Database credentials from Hostinger
- ✅ Local Git repository initialized

### Hostinger Account Setup
1. Log in to your Hostinger control panel
2. Verify PHP version is 8.1+
3. Verify Composer is installed
4. Verify Node.js is available
5. Create a MySQL database and user

---

## Local Preparation

### Step 1: Run Local Deployment Script
Run the PowerShell script to prepare your project:

```powershell
# In PowerShell (as Administrator)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\deploy-local.ps1
```

This script will:
- ✓ Initialize Git repository
- ✓ Install Composer dependencies
- ✓ Install npm dependencies
- ✓ Build Vite assets
- ✓ Generate app key
- ✓ Create .env.production template
- ✓ Create deployment package

### Step 2: Update .env.Production
Edit the generated `.env.production` file with your Hostinger details:

```env
APP_NAME="MEBS HR System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:xxxxxxxxxxxx  # Keep generated key

# Database credentials from Hostinger
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Email configuration (use your email service)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="MEBS HR System"
```

### Step 3: Get Hostinger Database Credentials
1. Open Hostinger control panel
2. Go to **Databases** section
3. Click **Create Database**
4. Name it (e.g., `mebs_hiyas`)
5. Create database user with full privileges
6. Note the credentials for .env.production

---

## Hostinger Setup

### Step 1: Configure Public Directory
1. In Hostinger control panel, go to **Domains**
2. Click your domain → **Manage**
3. Set **Public Directory** to `/public_html/mebs-hiyas/public`
   - This is important for security!
4. Save changes

### Step 2: Enable SSL Certificate
1. Go to **SSL/TLS** section
2. Enable Free SSL (Let's Encrypt)
3. Wait for activation (usually < 5 minutes)
4. Update `.env.production` to use `https://`

### Step 3: Enable SSH Access
1. Verify SSH access is enabled in control panel
2. Get SSH credentials from **Account Settings**
3. Note down:
   - SSH hostname
   - SSH username
   - SSH port (usually 22)

### Step 4: Create Upload Directory
Via File Manager or SSH:
```bash
mkdir -p public_html/mebs-hiyas
chmod 755 public_html/mebs-hiyas
```

---

## Server Deployment

### Option A: Using SFTP (FileZilla)

1. **Download FileZilla**: https://filezilla-project.org/
2. **Connect to Hostinger**:
   - Host: `sftp://your-server.com`
   - Username: Your Hostinger username
   - Password: Your password
   - Port: 22 (or as provided)

3. **Upload files**:
   - Navigate to `public_html/mebs-hiyas/` on remote
   - Drag the `mebs-hiyas-deployment` folder contents
   - Upload all files

4. **Upload .env**:
   - Copy `.env.production` to server
   - Rename to `.env`
   - Set permissions: `chmod 644 .env`

5. **Run SSH Commands** (in Hostinger terminal or FileZilla SSH):

### Option B: Using SSH (Recommended)

1. **Connect via SSH**:
   ```bash
   ssh your-username@your-server.com
   ```

2. **Navigate to deployment directory**:
   ```bash
   cd public_html/mebs-hiyas
   ```

3. **Upload via Git** (if using Git):
   ```bash
   git clone https://github.com/your-repo.git .
   ```

4. **Or upload via SFTP first**, then proceed to Step 5

### Step 5: Run Deployment Script

Once files are uploaded to `public_html/mebs-hiyas/`:

```bash
# Via SSH
ssh your-username@your-server.com
cd public_html/mebs-hiyas

# Make script executable
chmod +x deploy-server.sh

# Run deployment
bash deploy-server.sh
```

The script will automatically:
- ✓ Install PHP dependencies (Composer)
- ✓ Install Node dependencies and build assets
- ✓ Set correct permissions
- ✓ Generate app key (if needed)
- ✓ Run database migrations
- ✓ Optimize application
- ✓ Create storage symlink

---

## Post-Deployment

### Step 1: Verify Installation
```bash
# Check if files exist
ls -la public_html/mebs-hiyas/

# Check Laravel is working
php artisan --version

# Check application status
php artisan tinker
```

### Step 2: Test Application
1. Open your browser: `https://your-domain.com`
2. You should see the login page
3. Test login with test credentials

### Step 3: Set Up Cron Jobs
In Hostinger control panel, go to **Cron Jobs**:

Add this cron job:
```bash
* * * * * cd /home/your-user/public_html/mebs-hiyas && php artisan schedule:run >> /dev/null 2>&1
```

This runs Laravel's task scheduler every minute.

### Step 4: Configure Email
1. Get SMTP credentials from your email provider:
   - Gmail: Use App Passwords
   - SendGrid: Create API token
   - Mailgun: Get SMTP credentials

2. Update `.env`:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   ```

3. Test:
   ```bash
   php artisan tinker
   > Mail::raw('Test email', fn($m) => $m->to('test@example.com'));
   ```

### Step 5: Set Up Backups
Create a backup script in `scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/home/your-user/backups"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
DB_NAME="your_database_name"
DB_USER="your_db_user"
DB_PASS="your_db_password"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /home/your-user/public_html/mebs-hiyas

# Keep only last 7 backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Add to cron (weekly backup):
```bash
0 2 * * 0 bash /home/your-user/scripts/backup.sh
```

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan cache:clear
php artisan config:clear

# Check .env file
cat .env | grep -E "APP_|DB_"

# Verify permissions
chmod -R 755 storage bootstrap/cache
```

### Issue: Database Connection Failed

**Solution:**
```bash
# Verify .env database settings
cat .env | grep DB_

# Test database connection
php artisan tinker
> DB::connection()->getPdo();

# Check MySQL credentials
mysql -h localhost -u your_db_user -p your_database_name
```

### Issue: Assets Not Loading (CSS/JS)

**Solution:**
```bash
# Rebuild assets
npm run build

# Clear Vite manifest
rm public/build/manifest.json

# Rebuild again
npm run build

# Clear browser cache (Ctrl+Shift+Del)
```

### Issue: File Upload Not Working

**Solution:**
```bash
# Check storage permissions
chmod -R 755 storage

# Create storage symlink
php artisan storage:link

# Verify public/storage points to storage/app/public
ls -la public/storage
```

### Issue: Emails Not Sending

**Solution:**
```bash
# Test mail configuration
php artisan tinker
> Mail::raw('Test', fn($m) => $m->to('your-email@gmail.com'));

# Check mail logs
tail -f storage/logs/laravel.log | grep -i mail

# Verify SMTP credentials in .env
cat .env | grep MAIL_
```

### Issue: Permission Denied Errors

**Solution:**
```bash
# For storage directory
chmod -R 755 storage
chmod 644 storage/logs/*

# For bootstrap cache
chmod -R 755 bootstrap/cache

# For .env file
chmod 644 .env

# For public directory
chmod 755 public
```

---

## Emergency Rollback

If something goes wrong:

```bash
# Restore from backup
tar -xzf /home/your-user/backups/files_YYYY-MM-DD.tar.gz -C /

# Restore database
mysql -u your_db_user -p your_database_name < /home/your-user/backups/db_YYYY-MM-DD.sql

# Restart services (if SSH access to restart PHP-FPM)
sudo systemctl restart php-fpm
```

---

## Performance Optimization

### Enable Caching
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### Database Optimization
```bash
# Add indexes (already in migrations)
php artisan migrate

# Optimize table
php artisan tinker
> DB::statement('OPTIMIZE TABLE table_name;');
```

### Monitor Application
```bash
# Check application status
php artisan health

# View queue jobs
php artisan queue:list

# Monitor logs
tail -f storage/logs/laravel.log
```

---

## Support & Maintenance

### Regular Tasks
- ✓ Monitor `storage/logs/laravel.log` weekly
- ✓ Check database backups are working
- ✓ Update Laravel dependencies quarterly
- ✓ Review security logs (audit_logs table)

### Useful Commands
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrate only fresh
php artisan migrate:fresh --seed

# Check application health
php artisan health

# View application status
php artisan tinker
> Illuminate\Support\Facades\App::version();
```

### Get Help
- Laravel Docs: https://laravel.com/docs
- Hostinger Help: https://support.hostinger.com
- Check logs: `storage/logs/laravel.log`

---

## Deployment Checklist

- [ ] Domain registered and DNS configured
- [ ] Hostinger account created with Business plan
- [ ] Database created in Hostinger
- [ ] SSH/SFTP credentials obtained
- [ ] Local deploy script executed successfully
- [ ] .env.production updated with Hostinger details
- [ ] Public directory configured in Hostinger
- [ ] SSL certificate enabled
- [ ] Files uploaded via SFTP
- [ ] Server deployment script executed
- [ ] Application tested at https://yourdomain.com
- [ ] Login page displays correctly
- [ ] Database migrations completed
- [ ] Cron jobs configured
- [ ] Email configuration tested
- [ ] Backups configured
- [ ] Monitoring enabled

---

**Deployment completed!** 🎉

Your MEBS HR System is now live on Hostinger.
