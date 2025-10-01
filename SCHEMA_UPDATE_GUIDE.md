# Database Schema Update Guide

## Overview
This guide explains how to update the database schema to match all admin dashboard form fields.

## Current Issues Identified

### 1. Missing Database Columns
The admin forms have many fields that don't exist in the database:

#### Site Settings
- ❌ `description` (TEXT) - site description
- ❌ `language` (VARCHAR) - site language
- ❌ `timezone` (VARCHAR) - timezone setting
- ❌ `apple_touch_icon` (VARCHAR) - apple touch icon URL
- ❌ `title_template` (VARCHAR) - page title template

#### Navbar Settings
- ❌ `use_transparent_logo` (TINYINT) - whether to use transparent logo on scroll

#### Dropdown Settings
- ❌ `background_image` (VARCHAR) - dropdown background image URL
- ❌ `background_position` (VARCHAR) - background image position

#### Hero Slides
- ❌ `subtitle_color` (VARCHAR) - subtitle color
- ❌ `button1_text`, `button1_url` - first button
- ❌ `button2_text`, `button2_url` - second button

### 2. Missing Tables
- ❌ `navbar_settings` - May not exist in production
- ❌ `social_media` - Social media links
- ❌ `hero_settings` - Hero autoplay and duration settings
- ❌ `services_settings` - Services section title/subtitle
- ❌ `features_settings` - Features section title/subtitle
- ❌ `projects_settings` - Projects section title/subtitle
- ❌ `news_settings` - News section title/subtitle
- ❌ `component_visibility` - Toggle visibility of page sections

## Solution Files Created

### 1. `auto_update_schema.php` (RECOMMENDED)
**Purpose:** Automatically updates database schema with one click

**How to use:**
```bash
1. Upload to production server
2. Access: http://testing.catehotel.co.tz/auto_update_schema.php
3. Click "UPDATE DATABASE SCHEMA NOW"
4. Wait for completion
```

**What it does:**
- ✅ Adds all missing columns
- ✅ Creates all missing tables
- ✅ Inserts default data
- ✅ Preserves existing data (no data loss)
- ✅ Shows detailed progress report

### 2. `update_database_schema.sql`
**Purpose:** Manual SQL script for database updates

**How to use:**
```bash
1. Login to phpMyAdmin or MySQL client
2. Select database: cateeccx_lake_db
3. Import the SQL file
4. Execute all statements
```

### 3. `compare_schema.php`
**Purpose:** View current database schema

**How to use:**
```bash
Access: http://testing.catehotel.co.tz/compare_schema.php
```

Shows all tables and their columns for verification.

## Step-by-Step Instructions

### Step 1: Backup Database
**IMPORTANT:** Always backup before schema changes!

```bash
# Via command line:
mysqldump -u cateeccx_lake_admin -p cateeccx_lake_db > backup_before_schema_update.sql

# Or use phpMyAdmin Export feature
```

### Step 2: Run Schema Update
**Option A: Automatic (Recommended)**
1. Upload `auto_update_schema.php` to production
2. Access `http://testing.catehotel.co.tz/auto_update_schema.php`
3. Review what will be updated
4. Click "UPDATE DATABASE SCHEMA NOW"
5. Verify success messages

**Option B: Manual**
1. Upload `update_database_schema.sql`
2. Open phpMyAdmin
3. Select `cateeccx_lake_db` database
4. Go to SQL tab
5. Paste SQL from file
6. Execute

### Step 3: Verify Schema
```bash
Access: http://testing.catehotel.co.tz/compare_schema.php
```

Check that all tables and columns now exist.

### Step 4: Test Admin Dashboard
1. Go to admin dashboard
2. Open each section (Site Settings, Hero Slider, Services, etc.)
3. Make changes
4. Save
5. Reload page
6. Verify changes persisted

## What Gets Updated

### New Columns Added

```sql
site_settings:
  + description (TEXT)
  + language (VARCHAR)
  + timezone (VARCHAR)
  + apple_touch_icon (VARCHAR)
  + title_template (VARCHAR)

hero_slides:
  + subtitle_color (VARCHAR)
  + button1_text (VARCHAR)
  + button1_url (VARCHAR)
  + button2_text (VARCHAR)
  + button2_url (VARCHAR)

dropdown_settings:
  + background_image (VARCHAR)
  + background_position (VARCHAR)

navbar_settings:
  + use_transparent_logo (TINYINT)
```

### New Tables Created

```sql
navbar_settings (id, position, height, background_color, text_color, hover_color, use_transparent_logo)

social_media (id, facebook, twitter, instagram, linkedin, youtube, google_plus)

hero_settings (id, autoplay, duration)

services_settings (id, section_title, section_subtitle)

features_settings (id, title, subtitle)

projects_settings (id, section_title, section_subtitle)

news_settings (id, section_title, section_subtitle)

component_visibility (id, hero, services, about, features, projects, news, footer)
```

## After Schema Update

### Verify save_content.php Handles New Fields

The `save_content.php` file needs to be updated to save these new fields. Check that it includes:

1. **Site Settings** - All new columns
2. **Social Media** - Separate table handling
3. **Hero Settings** - Autoplay and duration
4. **Section Settings** - Titles and subtitles for each section
5. **Component Visibility** - Toggle sections on/off

### Expected Behavior After Update

✅ **Site Settings Tab:**
- Description field saves
- Language dropdown saves
- Timezone saves
- Apple touch icon URL saves
- Title template saves

✅ **Hero Slider:**
- Autoplay toggle works
- Slide duration saves
- Button1 and Button2 fields save
- Subtitle color saves

✅ **All Section Titles:**
- Services section title/subtitle editable
- Features section title/subtitle editable
- Projects section title/subtitle editable
- News section title/subtitle editable

✅ **Social Media:**
- All social media links save independently
- Appear on website footer correctly

✅ **Component Visibility:**
- Can hide/show sections
- Changes reflect on public website

## Troubleshooting

### Error: "Duplicate column name"
**Meaning:** Column already exists
**Solution:** Not an error, script skips it automatically

### Error: "Table already exists"
**Meaning:** Table was created before
**Solution:** Not an error, script skips it automatically

### Error: "Access denied"
**Meaning:** Database user lacks permissions
**Solution:**
```sql
GRANT ALL PRIVILEGES ON cateeccx_lake_db.* TO 'cateeccx_lake_admin'@'localhost';
FLUSH PRIVILEGES;
```

### Forms Still Show Default Data
**Cause:** `save_content.php` doesn't handle new fields yet
**Solution:** Update `save_content.php` to include new field handling (see next section)

## Next Steps

1. ✅ Run schema update script
2. ✅ Verify all tables/columns created
3. ⏳ Update `save_content.php` if needed
4. ⏳ Update `generate_site.php` if needed
5. ⏳ Test all admin forms
6. ⏳ Verify public website displays correctly

## Files Reference

- `auto_update_schema.php` - Automatic schema updater (one-click solution)
- `update_database_schema.sql` - Manual SQL script
- `compare_schema.php` - View current schema
- `diagnose_media.php` - Media library diagnostic
- `import_existing_media.php` - Import existing uploads into database
- `admin/save_content.php` - Content save/load controller
- `generate_site.php` - Website generator

## Support

If you encounter issues:
1. Check `compare_schema.php` to verify schema
2. Check browser console for JavaScript errors
3. Check PHP error logs for server errors
4. Run `diagnose_500_errors.php` to check API endpoints
