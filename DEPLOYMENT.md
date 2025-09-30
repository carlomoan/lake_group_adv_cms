# Deployment Guide

## Environment Setup

This system automatically detects whether it's running in development or production environment.

### Development Environment (Automatic Detection)
The system will use development settings when:
- Running on `localhost` or `127.0.0.1`
- File `.dev-environment` exists
- Running from `/home/` paths (local development)
- No `.production-environment` file exists

**Development Database Config:**
- Host: localhost
- Database: lake_db
- Username: root
- Password: 123456

### Production Environment

To deploy to production:

1. **Create Production Environment File:**
   ```bash
   touch .production-environment
   ```

2. **Verify Production Database Settings** in `config.php`:
   - Host: localhost
   - Database: cateeccx_lake_db
   - Username: cateeccx_lake_admin
   - Password: Lake@2025

3. **Ensure Production Database Exists:**
   - Create database `cateeccx_lake_db`
   - Create user `cateeccx_lake_admin` with password `Lake@2025`
   - Grant appropriate permissions

4. **Test the Setup:**
   ```bash
   curl http://yourdomain.com/admin/save_content.php
   ```

## File Structure
```
petroleum-gas/
├── config.php                 # Database configuration
├── admin/
│   └── save_content.php       # API endpoint
├── index.html                 # Public webpage
├── .production-environment    # Create this file for production
└── .dev-environment          # Optional: Force development mode
```

## Troubleshooting

### Database Connection Issues
1. Check if the database service is running
2. Verify database credentials in `config.php`
3. Ensure the database and user exist
4. Check file permissions

### Environment Detection Issues
- Check if `.production-environment` file exists for production
- Verify HTTP_HOST values match your domain
- Check error logs for configuration issues

### Testing
Use the debug script to test configuration:
```bash
php debug_db.php
```

## Security Notes
- The production database password is visible in `config.php`
- Consider using environment variables for sensitive data
- Ensure proper file permissions in production
- Remove debug files before going live