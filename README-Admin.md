# Petroleum & Gas Complete Content Management System

This is a comprehensive admin interface for managing all content on the Petroleum & Gas website, including hero sliders, services, projects, news, navigation menu, footer, colors, branding, and more.

## 🚀 Features

### Content Management
- **Hero Slider**: Manage multiple slides with titles, subtitles, descriptions, images, and call-to-action buttons
- **Services**: Add/edit service items with descriptions, images, and features
- **Projects**: Manage project portfolio with categories, descriptions, and links
- **News Articles**: Latest news with images, excerpts, and publication dates
- **About Section**: Company information with highlights and imagery

### Site Structure
- **Navigation Menu**: Dynamic menu management with dropdowns and nested items
- **Footer Management**: Configure footer content, contact info, and links
- **Media Library**: Upload and organize images and media files
- **Pages**: Create and manage additional pages with components

### Appearance & Branding
- **Colors & Branding**: Customize primary, secondary, and tertiary colors
- **Logo Management**: Upload and configure main and transparent logos
- **Social Media**: Configure social media links and icons

### Advanced Features
- **SEO Settings**: Meta descriptions, keywords, and analytics integration
- **Database Integration**: Full MySQL database support with JSON fallback
- **Export/Import**: Backup and restore all content
- **Real-time Preview**: Preview changes before publishing

## 📋 Prerequisites

- Node.js (v14 or higher)
- MySQL Server (v5.7 or higher)
- Web browser with modern JavaScript support

## 🛠️ Installation & Setup

### 1. Install Dependencies
```bash
npm install
```

### 2. Configure Environment
Copy and configure your environment variables:
```bash
cp .env.example .env
```

Edit `.env` file:
```env
# MySQL Configuration
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DATABASE=lake_db
MYSQL_USER=root
MYSQL_PASSWORD=your_password

# Server Configuration
PORT=3001
NODE_ENV=development

# Admin Credentials
ADMIN_USER=admin
ADMIN_PASS=changeme
```

### 3. Create Database
```sql
CREATE DATABASE lake_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Initialize Database
```bash
npm run init-db
```

This will:
- Create all necessary database tables
- Migrate existing content from JSON files
- Set up the admin system
- Display database statistics

### 5. Start the Server
```bash
npm start
```

The server will start on `http://localhost:3001`

## 🖥️ Admin Interface

### Accessing the Admin Panel
1. Open your browser and go to `http://localhost:3001/admin`
2. Login with your configured credentials (default: admin/changeme)
3. You'll see the comprehensive dashboard with all management options

### Admin Sections

#### 📊 Dashboard
- Overview of content statistics
- Quick action buttons
- Content counts and metrics

#### 🎯 Hero Slider Management
- Add/edit multiple hero slides
- Configure titles, subtitles, and descriptions
- Upload background images (recommended: 1920x1080px)
- Add call-to-action buttons with custom styling
- Set text colors (primary, secondary, tertiary, white, black, or custom)

#### 🔧 Services Management
- Manage service items with titles and descriptions
- Upload service images
- Configure service features and links
- Section title and subtitle customization

#### 💼 Projects Management
- Add project portfolio items
- Categorize projects
- Upload project images
- Set project links and descriptions
- Configure "More Projects" button

#### 📰 News Management
- Create and manage news articles
- Upload news images
- Set publication dates
- Add article excerpts and links

#### 🧭 Navigation Menu
- Dynamic menu management
- Support for dropdown menus (unlimited depth)
- Logo upload and configuration
- Logo dimensions and styling options

#### 🦶 Footer Management
- Configure footer content and description
- Manage contact information (phone, email, address)
- Add footer menu items
- Social media links

#### 📚 Media Library
- Upload images and media files
- Supported formats: JPG, PNG, GIF, WebP, SVG
- File size limit: 10MB per file
- Search and organize media files
- Automatic thumbnail generation

#### 🎨 Colors & Branding
- Customize primary, secondary, and tertiary colors
- Color picker with hex code input
- Real-time preview of color changes
- Navbar background color configuration
- Quote section styling

#### 📱 Social Media
- Configure social media links
- Facebook, Instagram, Google+ integration
- Social icons in header and footer

#### ⚙️ Site Settings
- Site title and basic information
- Favicon upload and management
- Footer copyright text
- Global site configurations

#### 🔍 SEO Settings
- Meta descriptions and keywords
- Google Analytics integration
- Search engine optimization settings
- Custom head and body code injection

#### 💾 Export & Backup
- Export all content as JSON
- Import content from backup files
- Database migration tools
- Content versioning support

## 🏗️ Database Structure

The system uses a flexible database schema:

### Core Tables
- `site_settings`: Global site configuration
- `content_storage`: JSON-based content storage for flexibility
- `media_library`: Uploaded media files and metadata
- `pages`: Additional pages and their content
- `seo_settings`: SEO and analytics configuration

### Key Features
- **JSON Storage**: Flexible content structure using MySQL JSON fields
- **Media Management**: Complete file upload and organization system
- **Version Control**: Track content changes and updates
- **Backup/Restore**: Full content export and import capabilities

## 🔄 Content Structure

The admin interface manages content that matches the structure used in `index.html`:

### Hero Slider
```json
{
  "hero": {
    "slides": [
      {
        "title": "Slide Title",
        "subtitle": "Slide Subtitle",
        "description": "Detailed description",
        "image": "/uploads/slide-image.jpg",
        "buttons": [
          {
            "text": "Button Text",
            "url": "#link",
            "style": "primary"
          }
        ]
      }
    ]
  }
}
```

### Services
```json
{
  "services": {
    "sectionTitle": "Our Core Services",
    "sectionSubtitle": "What we offer",
    "items": [
      {
        "title": "Service Name",
        "description": "Service description",
        "image": "/uploads/service-image.jpg",
        "features": ["Feature 1", "Feature 2"]
      }
    ]
  }
}
```

## 🎨 Customization

### Adding New Content Types
1. Extend the content structure in `data/content.json`
2. Add corresponding UI controls in `admin/index.html`
3. Update the Vue.js methods for CRUD operations
4. Modify the API endpoints if needed

### Custom Styling
- Edit the CSS variables in the admin interface
- Customize the admin theme colors
- Add new component templates

### API Endpoints
All admin operations use these API endpoints:
- `GET/POST /api/content` - Main content management
- `GET/POST /api/seo` - SEO settings
- `POST /api/upload` - File uploads
- `GET /api/media-library` - Media management
- `GET /api/export` - Content export

## 🔒 Security

### Authentication
- Basic HTTP authentication for admin areas
- Configurable admin credentials in `.env`
- Session-based access control

### File Upload Security
- File type restrictions (images only by default)
- File size limits (10MB maximum)
- Secure file naming and storage

### Data Validation
- Input sanitization and validation
- SQL injection prevention
- XSS protection

## 🐛 Troubleshooting

### Database Connection Issues
1. Verify MySQL server is running
2. Check database credentials in `.env`
3. Ensure database exists and user has permissions
4. Run `npm run init-db` to create tables

### File Upload Issues
1. Check upload directory permissions
2. Verify file size and type restrictions
3. Ensure adequate disk space

### Content Not Updating
1. Clear browser cache
2. Check browser developer console for errors
3. Verify API endpoints are responding
4. Check server logs for errors

## 📞 Support

For technical support or questions:
- Check the server logs: `tail -f logs/error.log`
- Verify database status: `GET /api/db-status`
- Review API documentation in `server.js`

## 🔄 Updates

To update the system:
1. Backup your content: Use the export feature
2. Update code files
3. Run `npm run init-db` to update database schema
4. Test all functionality
5. Import content if needed

---

**Note**: This admin system provides complete control over your website content. Always backup your content before making major changes, and test changes in a development environment first.