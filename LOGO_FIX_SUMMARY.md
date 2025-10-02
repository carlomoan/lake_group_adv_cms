# Logo Fields Fix Summary

## Problem
Admin dashboard showed blank page with error:
```
TypeError: can't access property "logoWidth", content.siteSettings.logo is undefined
```

## Root Cause
**Inconsistent data structure** between different parts of the system:
- Form inputs used FLAT structure: `content.siteSettings.logoMain`
- Default data had NESTED structure: `content.siteSettings.logo.logoMain`
- Some form fields used NESTED: `content.siteSettings.logo.logoWidth`
- Database load returned NESTED structure

## Solution
Standardized ALL logo fields to use **FLAT structure** throughout the entire system.

## Changes Made

### 1. admin/index.html (Lines changed)

**Line 1920-1924**: Changed default content structure from nested to flat
```javascript
// BEFORE (nested):
logo: {
    logoMain: '',
    logoTransparent: '',
    logoWidth: 150,
    logoHeight: 50,
    altText: 'Petroleum and Gas Logo'
}

// AFTER (flat):
logoMain: '',
logoTransparent: '',
logoWidth: 150,
logoHeight: 50,
logoAltText: 'Petroleum and Gas Logo',
```

**Line 473**: Logo width input
```html
<!-- BEFORE -->
<input v-model.number="content.siteSettings.logo.logoWidth" ...>

<!-- AFTER -->
<input v-model.number="content.siteSettings.logoWidth" ...>
```

**Line 477**: Logo height input
```html
<!-- BEFORE -->
<input v-model.number="content.siteSettings.logo.logoHeight" ...>

<!-- AFTER -->
<input v-model.number="content.siteSettings.logoHeight" ...>
```

**Line 483**: Logo alt text input
```html
<!-- BEFORE -->
<input v-model="content.siteSettings.logo.altText" ...>

<!-- AFTER -->
<input v-model="content.siteSettings.logoAltText" ...>
```

**Line 1829**: Fixed deepMerge context issue
```javascript
// BEFORE
this.content = this.deepMerge(this.content, result.content);

// AFTER
this.content = window.DATABASE_METHODS.deepMerge(this.content, result.content);
```

### 2. admin/save_content.php

**Lines 66-112**: Updated to save logo_height and logo_alt_text
```php
// Added to UPDATE statement:
logo_height = ?,
logo_alt_text = ?,

// Added fallback handling:
$logoHeight = $content['siteSettings']['logo']['logoHeight']
           ?? $content['siteSettings']['logoHeight']
           ?? 50;

$logoAltText = $content['siteSettings']['logo']['altText']
            ?? $content['siteSettings']['logoAltText']
            ?? 'Logo';
```

**Lines 604-616**: Changed GET response to return flat structure
```php
// BEFORE (nested):
'logo' => [
    'logoMain' => $siteSettings['logo_main'],
    'logoTransparent' => $siteSettings['logo_transparent'],
    'logoWidth' => $siteSettings['logo_width']
]

// AFTER (flat):
'logoMain' => $siteSettings['logo_main'],
'logoTransparent' => $siteSettings['logo_transparent'],
'logoWidth' => (int)$siteSettings['logo_width'],
'logoHeight' => (int)$siteSettings['logo_height'],
'logoAltText' => $siteSettings['logo_alt_text']
```

## Files to Upload to Production

1. **admin/index.html** - Contains all admin form fixes and deepMerge fix
2. **admin/save_content.php** - Contains save/load logic for logo fields

## Testing Steps

1. Upload both files to production server
2. Clear browser cache (Ctrl+Shift+Delete)
3. Reload admin dashboard at http://testing.catehotel.co.tz/admin/
4. Check console - should show NO errors
5. Navigate to "Navbar Settings" section
6. Enter logo URLs in the "Main Logo URL" and "Transparent Logo URL" fields
7. Click "Save Navbar Settings"
8. Check console for success message
9. Reload page - logo URLs should persist
10. Run generate_site.php to update public website
11. Check public website to verify logos appear

## Expected Behavior

✅ Admin dashboard loads without errors
✅ Logo form fields display correctly
✅ Logo URLs save to database
✅ Logo URLs persist after page reload
✅ deepMerge function works correctly when loading content
✅ All logo fields (main, transparent, width, height, alt text) save and load properly
