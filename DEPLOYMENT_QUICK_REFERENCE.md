# MEBS HR System - Quick Deployment Checklist

## Local Machine (Windows)

### 1. Generate Deployment Files
```powershell
# Open PowerShell as Administrator
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\deploy-local.ps1
```

### 2. Prepare Environment File
- Copy `env.production` to `.env.production`
- Edit with your Hostinger details:
  - DB_HOST: localhost
  - DB_DATABASE: your-db-name
  - DB_USERNAME: your-db-user
  - DB_PASSWORD: your-db-password
  - APP_URL: https://your-domain.com
  - MAIL credentials

### 3. Upload to Hostinger
**Via FileZilla SFTP:**
- Host: sftp://your-server.com
- Upload `mebs-hiyas-deployment` folder to `public_html/`
- Upload `.env.production` to server and rename to `.env`

---

## Hostinger Control Panel

### 1. Create Database
- Go to **Databases**
- Click **Create Database**
- Name: `mebs_hiyas` (or your preference)
- Create user with full privileges
- Note credentials

### 2. Configure Domain
- Go to **Domains**
- Select your domain
- Set **Public Directory** to `/public_html/mebs-hiyas/public`
- Save

### 3. Enable SSL
- Go to **SSL/TLS**
- Enable Free SSL (Let's Encrypt)
- Wait for activation

### 4. Enable SSH
- Go to **Account Settings**
- Note SSH credentials
- Verify SSH is enabled

---

## Hostinger Server (via SSH)

### 1. Connect
```bash
ssh username@your-server.com
cd public_html/mebs-hiyas
```

### 2. Run Deployment
```bash
chmod +x deploy-server.sh
bash deploy-server.sh
```

### 3. Set Cron Job
In Hostinger **Cron Jobs** panel, add:
```bash
* * * * * cd /home/username/public_html/mebs-hiyas && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Verify
```bash
php artisan --version
php artisan tinker
> DB::connection()->getPdo();
```

---

## Testing

### 1. Access Application
- Open: `https://your-domain.com`
- Should see login page

### 2. Test Database Connection
```bash
php artisan tinker
> DB::connection()->getPdo();
> DB::select('SELECT 1');
```

### 3. Test Email
```bash
php artisan tinker
> Mail::raw('Test email', fn($m) => $m->to('your-email@gmail.com'));
```

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Troubleshooting Quick Fixes

### 500 Error
```bash
php artisan cache:clear
php artisan config:clear
tail -f storage/logs/laravel.log
```

### Database Error
```bash
cat .env | grep DB_
mysql -h localhost -u user -p database_name
```

### CSS/JS Not Loading
```bash
npm run build
rm public/build/manifest.json
npm run build
```

### Permission Issues
```bash
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

---

## File Locations

**On Hostinger Server:**
- App Root: `/home/username/public_html/mebs-hiyas/`
- Public Directory: `/home/username/public_html/mebs-hiyas/public/`
- Logs: `/home/username/public_html/mebs-hiyas/storage/logs/laravel.log`
- Database: Configured in `.env`
- Cache: `/home/username/public_html/mebs-hiyas/bootstrap/cache/`

---

## Important Files to Keep Backed Up

- `.env` - Environment configuration
- `database/` - Migration files
- `storage/logs/` - Application logs
- Database dump - Regular backups

---

## Post-Deployment Maintenance

- ✓ Check logs daily: `tail -f storage/logs/laravel.log`
- ✓ Database backups: Weekly
- ✓ Update Laravel: Quarterly (with testing)
- ✓ Monitor disk usage: Hostinger panel
- ✓ Review audit logs: In application

---

## Useful Commands

```bash
# SSH into server
ssh username@your-server.com

# Navigate to app
cd public_html/mebs-hiyas

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Check application status
php artisan health

# View logs
tail -f storage/logs/laravel.log

# Access tinker shell
php artisan tinker

# Restart queue (if using)
php artisan queue:restart
```

---

## Emergency Support

- **Laravel Docs**: https://laravel.com/docs
- **Hostinger Support**: https://support.hostinger.com
- **Application Logs**: `storage/logs/laravel.log`
- **Check Status**: `php artisan health`

---

**Created on**: 2026-02-03
**Version**: 1.0
