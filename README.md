# Petroleum Gas Website - WordPress to PHP Conversion

This project converts a WordPress petroleum/gas industry website to a standalone PHP application with custom database structure, removing all WordPress dependencies.

## Features

- ✅ **No WordPress Dependencies** - Completely standalone PHP application
- ✅ **Custom Database Schema** - Optimized tables for better performance
- ✅ **Security Features** - CSRF protection, input validation, SQL injection prevention
- ✅ **Responsive Design** - Mobile-friendly layout
- ✅ **E-commerce Ready** - Product management system
- ✅ **Contact Forms** - Built-in contact form handling
- ✅ **SEO Optimized** - Meta tags, clean URLs, structured data
- ✅ **Admin Panel Ready** - User roles and permissions system

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Web server (Apache/Nginx)
- mod_rewrite enabled (for clean URLs)

## Installation

### 1. Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE petroleum_gas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p petroleum_gas_db < database_schema.sql
```

### 2. Configuration

1. Update database credentials in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'petroleum_gas_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Update site URL and paths:
```php
define('SITE_URL', 'http://your-domain.com');
define('SITE_PATH', '/path/to/your/website');
```

3. Generate security keys:
```php
define('AUTH_KEY', 'your-unique-auth-key-here');
define('SECURE_AUTH_KEY', 'your-unique-secure-auth-key-here');
define('LOGGED_IN_KEY', 'your-unique-logged-in-key-here');
define('NONCE_KEY', 'your-unique-nonce-key-here');
```

### 3. File Permissions

Set proper permissions for directories:
```bash
chmod 755 assets/
chmod 777 uploads/
chmod 777 cache/
chmod 777 logs/
```

## Default Login

After installation, you can log in with:
- **Username:** admin
- **Email:** admin@petroleumgas.com
- **Password:** admin123

**⚠️ Important:** Change the default password immediately after installation!

## Database Tables

The custom database schema includes these main tables:

- **site_options** - Site configuration and settings
- **pages** - Static pages content
- **posts** - Blog posts and news
- **products** - E-commerce products
- **product_categories** - Product organization
- **users** - User accounts and profiles
- **menu_items** - Navigation menus
- **contact_submissions** - Contact form data
- **meta_data** - Custom fields for any object
- **seo_data** - SEO metadata

## Security Features

- CSRF token protection
- SQL injection prevention
- XSS protection
- Rate limiting
- Input validation and sanitization
- Security event logging

## File Structure

```
petroleum-gas/
├── index.php                 # Main entry point
├── config.php               # Configuration settings
├── database_schema.sql      # Database structure
├── README.md               # This file
├── includes/               # Core functionality
│   ├── functions.php       # Utility functions
│   ├── database.php        # Database helpers
│   └── security.php        # Security functions
├── assets/                 # Static assets (you need to add these)
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Image assets
├── uploads/               # User uploaded files
├── cache/                 # Cache files
└── logs/                  # Log files
```

## What's Changed from WordPress

### Removed:
- All WordPress core files and functions
- WordPress plugins (Contact Form 7, WooCommerce, etc.)
- WordPress themes and template system
- WordPress emoji and block editor scripts
- WordPress REST API endpoints
- All wp-content dependencies

### Replaced with:
- Custom PHP functions and classes
- Direct database queries with PDO
- Custom security and validation
- Standalone HTML/CSS/JavaScript
- Custom routing system
- Native PHP session management

### Benefits:
- **Faster Performance** - No WordPress overhead
- **Better Security** - Custom security implementations
- **Easier Maintenance** - No plugin conflicts or updates
- **Full Control** - Complete customization freedom
- **Lighter Footprint** - Significantly smaller codebase

## Quick Start

1. **Setup Database:**
   ```bash
   mysql -u root -p < database_schema.sql
   ```

2. **Configure:**
   ```bash
   # Edit config.php with your database credentials
   nano config.php
   ```

3. **Set Permissions:**
   ```bash
   chmod 755 .
   mkdir -p uploads cache logs assets
   chmod 777 uploads cache logs
   ```

4. **Access Website:**
   - Visit your domain in browser
   - Login with admin/admin123
   - Change default password

## Next Steps

After installation, you should:

1. **Add Assets** - Copy CSS, JavaScript, and image files to `assets/` directory
2. **Customize Design** - Modify the HTML/CSS to match your branding
3. **Add Content** - Use the database to add your pages, products, and content
4. **Configure Email** - Set up SMTP settings for contact forms
5. **SSL Certificate** - Enable HTTPS for security
6. **Backup System** - Implement regular database backups

This conversion gives you a clean, WordPress-free website with all the functionality you need for a petroleum/gas industry site.