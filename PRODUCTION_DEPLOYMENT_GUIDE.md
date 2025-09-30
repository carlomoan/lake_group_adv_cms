# Production Deployment Guide - HTTP 500 Error Fix

## 🔴 Current Problem

You're seeing these errors on production (`testing.catehotel.co.tz`):
```
Error loading media library: Error: HTTP 500
Error loading content: Error: Database response not ok: 500
```

This means the PHP scripts are failing on your production server. Follow these steps to fix it.

## 🔧 Step-by-Step Fix

### Step 1: Run Diagnostic Script

1. **Upload `admin/check_production.php`** to your production server
2. **Access it in browser**: `http://testing.catehotel.co.tz/admin/check_production.php`
3. **Check the output** for any ❌ marks - these indicate what needs fixing

### Step 2: Create Production Database

On your production server, run these MySQL commands:

```sql
-- Login to MySQL
mysql -u root -p

-- Create production database
CREATE DATABASE IF NOT EXISTS cateeccx_lake_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user
CREATE USER IF NOT EXISTS 'cateeccx_lake_admin'@'localhost' IDENTIFIED BY 'Lake@2025';

-- Grant privileges
GRANT ALL PRIVILEGES ON cateeccx_lake_db.* TO 'cateeccx_lake_admin'@'localhost';
FLUSH PRIVILEGES;

-- Verify it works
USE cateeccx_lake_db;
SHOW TABLES;
```

### Step 3: Import Database Structure

Export from your development environment:
```bash
mysqldump -u root -p123456 lake_db > lake_db_export.sql
```

Upload `lake_db_export.sql` to your production server, then import:
```bash
mysql -u cateeccx_lake_admin -pLake@2025 cateeccx_lake_db < lake_db_export.sql
```

### Step 4: Verify Database Credentials

On production server, check `config.php` has correct production settings:

```php
// Production environment
return [
    'host' => 'localhost',
    'dbname' => 'cateeccx_lake_db',
    'username' => 'cateeccx_lake_admin',
    'password' => 'Lake@2025',
    'environment' => 'production'
];
```

### Step 5: Create Production Environment File

On your production server:
```bash
cd /path/to/petroleum-gas/
touch .production-environment
```

This tells the system to use production database credentials.

### Step 6: Test Database Connection

Create a test file `test_db.php` on production:

```php
<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    echo "✅ Database connection successful!\n";
    echo "Database: $dbname\n";
    echo "User: $username\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
```

Access: `http://testing.catehotel.co.tz/test_db.php`

### Step 7: Check File Permissions

On production server:
```bash
# Make sure PHP can write to these files/directories
chmod 755 /path/to/petroleum-gas/admin
chmod 644 /path/to/petroleum-gas/admin/save_content.php
chmod 644 /path/to/petroleum-gas/generate_site.php
chmod 644 /path/to/petroleum-gas/config.php

# Ensure these files are writable
chmod 666 /path/to/petroleum-gas/index_generated.html
chmod 666 /path/to/petroleum-gas/index.php
```

### Step 8: Check PHP Error Logs

Most common log locations:
- `/var/log/apache2/error.log`
- `/var/log/nginx/error.log`
- `/var/log/php/error.log`
- Check `phpinfo()` for exact location

Look for database connection errors or PHP fatal errors.

## 🔍 Common Production Issues & Solutions

### Issue 1: Database User Doesn't Exist
**Error**: `Access denied for user 'cateeccx_lake_admin'@'localhost'`
**Fix**: Run Step 2 above to create the user

### Issue 2: Database Doesn't Exist
**Error**: `Unknown database 'cateeccx_lake_db'`
**Fix**: Run Step 2 and Step 3 above

### Issue 3: Wrong Password
**Error**: `Access denied for user ... (using password: YES)`
**Fix**: Update password in `config.php` or reset user password:
```sql
ALTER USER 'cateeccx_lake_admin'@'localhost' IDENTIFIED BY 'Lake@2025';
FLUSH PRIVILEGES;
```

### Issue 4: Tables Don't Exist
**Error**: `Table 'cateeccx_lake_db.site_settings' doesn't exist`
**Fix**: Import database structure (Step 3)

### Issue 5: Wrong Environment Detection
**Symptom**: Using dev database instead of production
**Fix**:
```bash
# Ensure this file exists on production
touch .production-environment

# Remove dev environment file if it exists
rm .dev-environment
```

### Issue 6: PHP Extensions Missing
**Error**: `Call to undefined function PDO::__construct()`
**Fix**: Install PHP MySQL extension:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-pdo

# CentOS/RHEL
sudo yum install php-mysqlnd

# Restart web server
sudo service apache2 restart
# or
sudo service nginx restart && sudo service php-fpm restart
```

## ✅ Verification Checklist

After completing the steps, verify:

- [ ] `check_production.php` shows all ✅ marks
- [ ] Can access admin dashboard without errors
- [ ] Can save content successfully
- [ ] Public website shows database content
- [ ] No HTTP 500 errors in browser console
- [ ] No PHP errors in server error logs

## 📞 If Still Not Working

1. **Check browser console** for exact error messages
2. **Check server error logs** for PHP errors
3. **Run diagnostic script** and send output
4. **Verify all credentials** match between config.php and database

## 🎯 Quick Fix Command Summary

```bash
# On production server, run all at once:

# 1. Create database and user
mysql -u root -p << 'EOF'
CREATE DATABASE IF NOT EXISTS cateeccx_lake_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'cateeccx_lake_admin'@'localhost' IDENTIFIED BY 'Lake@2025';
GRANT ALL PRIVILEGES ON cateeccx_lake_db.* TO 'cateeccx_lake_admin'@'localhost';
FLUSH PRIVILEGES;
EOF

# 2. Import database
mysql -u cateeccx_lake_admin -pLake@2025 cateeccx_lake_db < lake_db_export.sql

# 3. Create environment file
touch .production-environment

# 4. Test connection
php -r "require 'config.php'; \$pdo = new PDO('mysql:host='.\$host.';dbname='.\$dbname, \$username, \$password); echo 'Success!';"
```

## 🚀 Expected Result

After fixing, you should see in browser console:
```
Database methods loaded and ready for injection ✅
Complete Petroleum & Gas CMS loaded - Full website control ready! ✅
✅ Media library loaded: X files ✅
Using database integration for save ✅
Content saved successfully to database! ✅
```

No more HTTP 500 errors!