# Petroleum Gas Website - Complete Deployment Guide

## 🎯 System Overview

This website uses a **dynamic content generation system** that creates static HTML files pre-populated with database content. This approach ensures:

- ✅ **Instant Loading**: Content loads immediately (no API delays)
- ✅ **SEO Friendly**: Search engines see actual content in HTML
- ✅ **Reliable**: Works even if JavaScript is disabled
- ✅ **Fast**: No database queries during page visits
- ✅ **Auto-Updating**: Regenerates when admin changes content

## 🏗️ System Architecture

```
Database Content → PHP Generator → Static HTML File → User's Browser
     ↑                                      ↓
Admin Dashboard ←------------------------→ Generated Website
```

### Key Components:

1. **`generate_site.php`** - Fetches database content and creates HTML
2. **`index.php`** - Redirects to generated HTML file
3. **`index_generated.html`** - Auto-generated website with database content
4. **`admin/save_content.php`** - Saves content + triggers regeneration

## 🚀 Deployment Instructions

### Development Environment (Automatic Detection)
The system automatically uses development settings when:
- Running on `localhost` or `127.0.0.1`
- File `.dev-environment` exists
- Running from `/home/` paths (local development)
- No `.production-environment` file exists

**Development Database Config:**
- Host: localhost
- Database: lake_db
- Username: root
- Password: 123456

### Production Deployment

#### Step 1: Upload Files
Upload all files to your production server web directory.

#### Step 2: Configure Environment
```bash
# Create production environment flag
touch .production-environment
```

#### Step 3: Database Setup
Ensure your production database exists with these settings (defined in `config.php`):
- Host: localhost
- Database: cateeccx_lake_db
- Username: cateeccx_lake_admin
- Password: Lake@2025

#### Step 4: Generate Initial Website
```bash
php generate_site.php
```

#### Step 5: Test the System
```bash
# Test API endpoint
curl http://yourdomain.com/admin/save_content.php

# Test website loads database content
curl http://yourdomain.com/
```

## 📁 File Structure
```
petroleum-gas/
├── config.php                 # Environment & database configuration
├── generate_site.php          # Website generator (NEW)
├── index.php                  # Redirect to generated site (NEW)
├── index_generated.html       # Auto-generated website (NEW)
├── index_template.html        # Template for generation (AUTO-CREATED)
├── admin/
│   └── save_content.php       # API endpoint (ENHANCED)
├── index_static.html          # Original static template (BACKUP)
├── index_old.php              # Old PHP version (BACKUP)
├── .production-environment    # Production flag (CREATE FOR PROD)
└── DEPLOYMENT.md              # This documentation
```

## ⚙️ How It Works

### Content Update Flow:
1. **Admin saves content** via dashboard
2. **`save_content.php`** saves to database
3. **Auto-triggers** `generate_site.php`
4. **New HTML generated** with fresh database content
5. **Website instantly updated** - users see new content

### Generated Content Features:
- ✅ **HTML Title**: Updates with database site title
- ✅ **Pre-loaded Data**: All content embedded as JavaScript
- ✅ **Vue.js Ready**: Template still works with Vue.js for interactions
- ✅ **Fallback Support**: Falls back to API if generation fails

## 🧪 Testing & Troubleshooting

### Test Website Generation
```bash
php generate_site.php
```
Expected output:
```
📡 Connected to database (lake_db)
📦 Content loaded from database
   - Site Title: YOUR_SITE_TITLE
   - Hero Slides: X
   - Services: X
   - Projects: X
✅ Generated website saved to: index_generated.html
```

### Test Auto-Regeneration
```bash
# Save content via API (triggers auto-regeneration)
curl -X POST -H "Content-Type: application/json" \
  -d '{"content":{"siteSettings":{"siteTitle":"TEST TITLE"}}}' \
  http://localhost/admin/save_content.php
```

### Common Issues

#### 1. Website Shows Old Content
- **Check**: Is `index.php` redirecting to `index_generated.html`?
- **Fix**: Run `php generate_site.php` manually

#### 2. Database Connection Failed
- **Check**: Database credentials in `config.php`
- **Check**: Database service is running
- **Check**: Environment detection (dev vs production)

#### 3. Auto-Regeneration Not Working
- **Check**: `shell_exec` is enabled in PHP
- **Check**: File permissions allow writing `index_generated.html`
- **Check**: No PHP errors in `admin/save_content.php`

#### 4. Environment Detection Issues
- **Development**: Remove `.production-environment` file
- **Production**: Create `.production-environment` file
- **Force Dev**: Create `.dev-environment` file

### Manual Regeneration
If auto-regeneration fails, manually regenerate:
```bash
php generate_site.php
```

## 🔒 Security Notes

- Production database password is in `config.php` - consider environment variables
- Ensure proper file permissions (644 for files, 755 for directories)
- Remove any debug files before going live
- Consider restricting access to `admin/` directory

## 🎉 Benefits of This Approach

1. **Performance**: Website loads instantly with zero database queries
2. **SEO**: Search engines see actual content, not placeholder text
3. **Reliability**: Works even if database is temporarily unavailable
4. **User Experience**: No "loading..." states or content flashes
5. **Admin Friendly**: Content updates are instant and automatic

## 🚀 Production Checklist

- [ ] Upload all files to production server
- [ ] Create `.production-environment` file
- [ ] Verify production database connection
- [ ] Run `php generate_site.php`
- [ ] Test website shows database content
- [ ] Test admin can save content and auto-regeneration works
- [ ] Remove any debug/test files
- [ ] Set proper file permissions

**🎯 Your website now delivers database content instantly to every visitor!**