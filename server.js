const express = require('express');
const fs = require('fs');
const path = require('path');
const cors = require('cors');
const multer = require('multer');
require('dotenv').config();

// Simple HTTP Basic Auth middleware
const basicAuth = (req, res, next) => {
  const user = process.env.ADMIN_USER || 'admin';
  const pass = process.env.ADMIN_PASS || 'changeme';
  const auth = req.headers['authorization'];
  if (!auth || !auth.startsWith('Basic ')) {
    res.set('WWW-Authenticate', 'Basic realm="Admin Area"');
    return res.status(401).send('Authentication required');
  }
  const b64 = auth.split(' ')[1];
  const [u, p] = Buffer.from(b64, 'base64').toString().split(':');
  if (u !== user || p !== pass) {
    res.set('WWW-Authenticate', 'Basic realm="Admin Area"');
    return res.status(401).send('Invalid credentials');
  }
  next();
};

const app = express();
const port = process.env.PORT || 3000;


app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const publicDir = path.join(__dirname);
const dataFile = path.join(__dirname, 'data', 'content.json');

// optional DB-backed store
const db = require('./db');

// static files (serve theme files and uploads)
app.use('/', express.static(publicDir));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// public content endpoint (no auth required for frontend)
app.get('/api/content-public', async (req, res) => {
  try {
    if (db.isConfigured()) {
      const data = await db.getPublicContent();
      if (data) return res.json(data);
      // fallback to file if table empty
    }
    const raw = fs.readFileSync(dataFile, 'utf8');
    res.json(JSON.parse(raw));
  } catch (e) {
    res.status(500).json({ error: 'cannot read content', detail: e.message });
  }
});

// protect admin and API routes
app.use('/admin', basicAuth);
app.use('/api', basicAuth);

// get content (protected)
app.get('/api/content', async (req, res) => {
  try {
    if (db.isConfigured()) {
      const data = await db.getContent();
      if (data) return res.json(data);
      // fallback to file if table empty
    }
    const raw = fs.readFileSync(dataFile, 'utf8');
    res.json(JSON.parse(raw));
  } catch (e) {
    res.status(500).json({ error: 'cannot read content', detail: e.message });
  }
});

// update content (simple overwrite)
app.post('/api/content', async (req, res) => {
  const body = req.body;
  try {
    if (db.isConfigured()) {
      await db.saveContent(body);
      return res.json({ ok: true, source: 'db' });
    }
    fs.writeFileSync(dataFile, JSON.stringify(body, null, 2), 'utf8');
    res.json({ ok: true, source: 'file' });
  } catch (e) {
    res.status(500).json({ error: 'cannot write content', detail: e.message });
  }
});

// simple file upload for images
const uploadDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadDir)) fs.mkdirSync(uploadDir);
const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDir),
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    const cleanName = file.originalname.replace(/\s+/g, '-').replace(/[^a-zA-Z0-9.-]/g, '');
    cb(null, uniqueSuffix + '-' + cleanName);
  }
});

const upload = multer({
  storage: storage,
  limits: {
    fileSize: 10 * 1024 * 1024 // 10MB limit
  },
  fileFilter: function (req, file, cb) {
    // Allow images and some document types
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Only image files (JPEG, PNG, GIF, WebP, SVG) are allowed!'), false);
    }
  }
});

app.post('/api/upload', upload.single('file'), async (req, res) => {
  if (!req.file) return res.status(400).json({ error: 'no file uploaded' });

  const rel = '/uploads/' + req.file.filename;

  // Save media file info to database if configured
  try {
    if (db.isConfigured()) {
      await db.saveMediaFile({
        filename: req.file.filename,
        originalName: req.file.originalname,
        path: rel,
        size: req.file.size,
        mimeType: req.file.mimetype
      });
    }
  } catch (error) {
    console.warn('Failed to save media file to database:', error.message);
  }

  res.json({ url: rel, filename: req.file.filename, size: req.file.size });
});

// admin UI
app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'admin', 'index.html'));
});

// health
app.get('/ping', (req, res) => res.send('pong'));

// DB status with enhanced information
app.get('/api/db-status', async (req, res) => {
  try {
    if (!db.isConfigured()) return res.json({ configured: false, storage: 'file' });

    await db.init();
    const stats = await db.getStats();

    res.json({
      configured: true,
      storage: 'mysql',
      stats: stats,
      timestamp: new Date().toISOString()
    });
  } catch (e) {
    res.json({ configured: false, error: e.message, storage: 'file' });
  }
});

// Media files API
app.get('/api/media', async (req, res) => {
  try {
    if (!db.isConfigured()) {
      return res.json({ files: [], message: 'Database not configured' });
    }

    const limit = parseInt(req.query.limit) || 50;
    const offset = parseInt(req.query.offset) || 0;

    const files = await db.getMediaFiles(limit, offset);
    res.json({ files, count: files.length });
  } catch (e) {
    res.status(500).json({ error: 'Failed to get media files', detail: e.message });
  }
});

// Migrate content from file to database
app.post('/api/migrate', async (req, res) => {
  try {
    if (!db.isConfigured()) {
      return res.status(400).json({ error: 'Database not configured' });
    }

    await db.migrateFromFile(dataFile);
    res.json({ success: true, message: 'Content migrated to database successfully' });
  } catch (e) {
    res.status(500).json({ error: 'Migration failed', detail: e.message });
  }
});

// Enhanced Media Library API
app.get('/api/media-library', async (req, res) => {
  try {
    const mediaFiles = [];

    // Read uploads directory
    if (fs.existsSync(uploadDir)) {
      const files = fs.readdirSync(uploadDir);
      files.forEach(file => {
        const filePath = path.join(uploadDir, file);
        const stats = fs.statSync(filePath);

        if (stats.isFile()) {
          mediaFiles.push({
            id: file,
            name: file,
            url: `/uploads/${file}`,
            size: stats.size,
            type: path.extname(file).toLowerCase(),
            uploadDate: stats.mtime
          });
        }
      });
    }

    // Sort by upload date (newest first)
    mediaFiles.sort((a, b) => new Date(b.uploadDate) - new Date(a.uploadDate));

    res.json(mediaFiles);
  } catch (e) {
    res.status(500).json({ error: 'Failed to get media library', detail: e.message });
  }
});

// Delete media file
app.delete('/api/media/:filename', async (req, res) => {
  try {
    const filename = req.params.filename;
    const filePath = path.join(uploadDir, filename);

    if (fs.existsSync(filePath)) {
      fs.unlinkSync(filePath);

      // Remove from database if configured
      if (db.isConfigured()) {
        try {
          await db.deleteMediaFile(filename);
        } catch (error) {
          console.warn('Failed to delete from database:', error.message);
        }
      }

      res.json({ success: true, message: 'File deleted successfully' });
    } else {
      res.status(404).json({ error: 'File not found' });
    }
  } catch (e) {
    res.status(500).json({ error: 'Failed to delete file', detail: e.message });
  }
});

// Pages Management API
const pagesFile = path.join(__dirname, 'data', 'pages.json');

// Ensure pages.json exists
if (!fs.existsSync(pagesFile)) {
  const defaultPages = [
    {
      id: 1,
      title: 'Homepage',
      slug: 'home',
      content: '<h1>Welcome to Homepage</h1><p>This is your homepage content.</p>',
      status: 'published',
      template: 'default',
      metaDescription: 'Welcome to our homepage',
      components: [],
      lastModified: new Date().toISOString()
    }
  ];
  fs.writeFileSync(pagesFile, JSON.stringify(defaultPages, null, 2));
}

// Component templates for page generation
const componentTemplates = {
  hero: `
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">{{ pageTitle }}</h1>
            <p class="hero-subtitle">{{ metaDescription || 'Welcome to our page' }}</p>
            <div class="hero-actions">
                <button class="btn btn-primary">Get Started</button>
                <button class="btn btn-secondary">Learn More</button>
            </div>
        </div>
    </section>`,

  cards: `
    <!-- Card Grid Section -->
    <section class="cards-section">
        <div class="container">
            <h2 class="section-title">Features</h2>
            <div class="cards-grid">
                <div class="card" v-for="n in 3" :key="n">
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="card-title">Feature {{ n }}</h3>
                    <p class="card-description">Description of feature {{ n }} goes here.</p>
                </div>
            </div>
        </div>
    </section>`,

  accordion: `
    <!-- Accordion Section -->
    <section class="accordion-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            <div class="accordion">
                <div class="accordion-item" v-for="(item, index) in accordionItems" :key="index">
                    <div class="accordion-header" @click="toggleAccordion(index)">
                        <h3>{{ item.title }}</h3>
                        <i class="fas fa-chevron-down" :class="{ 'rotate': item.open }"></i>
                    </div>
                    <div class="accordion-content" v-show="item.open">
                        <p>{{ item.content }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>`,

  form: `
    <!-- Contact Form Section -->
    <section class="form-section">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <form class="contact-form" @submit.prevent="submitForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" v-model="form.name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" v-model="form.email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea v-model="form.message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </section>`,

  datatable: `
    <!-- Data Table Section -->
    <section class="datatable-section">
        <div class="container">
            <h2 class="section-title">Data Overview</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tableData" :key="item.id">
                            <td>{{ item.name }}</td>
                            <td>{{ item.position }}</td>
                            <td>{{ item.department }}</td>
                            <td>{{ item.email }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>`
};

// Generate page file with components
async function generatePageFile(page) {
  try {
    // Create pages directory if it doesn't exist
    const pagesDir = path.join(__dirname, 'pages');
    if (!fs.existsSync(pagesDir)) {
      fs.mkdirSync(pagesDir, { recursive: true });
    }

    // Generate component HTML
    let componentHTML = '';
    if (page.components && page.components.length > 0) {
      componentHTML = page.components.map(componentType => {
        return componentTemplates[componentType] || '';
      }).join('\\n');
    }

    // Base HTML template with inherited navbar and footer
    const pageHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${page.title}</title>
    <meta name="description" content="${page.metaDescription || page.title}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400&subset=latin" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,900&subset=latin" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <style>
        /* Page-specific styles */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero-title { font-size: 3rem; margin-bottom: 1rem; }
        .hero-subtitle { font-size: 1.2rem; margin-bottom: 2rem; }
        .section-title { text-align: center; margin-bottom: 3rem; font-size: 2.5rem; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; margin: 0 10px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-secondary { background: transparent; color: white; border: 2px solid white; }
    </style>
</head>
<body>
    <div id="app">
        <!-- Inherited Navigation -->
        <header class="l-header">
            <div class="top-bar-container contain-to-grid sticky">
                <nav class="top-bar" data-topbar="">
                    <ul class="title-area">
                        <li class="name">
                            <h1>
                                <a href="/" rel="home">
                                    <img class="logo" alt="Petroleum & Gas" src="/uploads/Lake-Logos-ALL-36-768x443.png">
                                </a>
                            </h1>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content" style="margin-top: 80px;">
            ${componentHTML}
            ${page.content ? `
            <section class="custom-content">
                <div class="container">
                    ${page.content}
                </div>
            </section>
            ` : ''}
        </main>

        <!-- Inherited Footer -->
        <footer class="l-footer">
            <div class="footer-content">
                <div class="container">
                    <p>&copy; 2024 Petroleum & Gas. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        const { createApp } = Vue;
        createApp({
            data() {
                return {
                    pageTitle: '${page.title}',
                    accordionItems: [
                        { title: 'What services do you offer?', content: 'We offer comprehensive solutions.', open: false }
                    ],
                    form: { name: '', email: '', message: '' },
                    tableData: [
                        { id: 1, name: 'John Doe', position: 'Manager', department: 'Operations', email: 'john@company.com' }
                    ]
                }
            },
            methods: {
                toggleAccordion(index) {
                    this.accordionItems[index].open = !this.accordionItems[index].open;
                },
                submitForm() {
                    alert('Form submitted!');
                    this.form = { name: '', email: '', message: '' };
                }
            }
        }).mount('#app');
    </script>
</body>
</html>`;

    // Write the page file
    const filePath = path.join(pagesDir, page.slug + '.html');
    fs.writeFileSync(filePath, pageHTML);

    console.log('✅ Generated page file: ' + filePath);
  } catch (error) {
    console.error('Error generating page file:', error);
    throw error;
  }
}

// Get all pages
app.get('/api/pages', async (req, res) => {
  try {
    const raw = fs.readFileSync(pagesFile, 'utf8');
    const pages = JSON.parse(raw);
    res.json(pages);
  } catch (e) {
    res.status(500).json({ error: 'Cannot read pages', detail: e.message });
  }
});

// Get single page
app.get('/api/pages/:id', async (req, res) => {
  try {
    const raw = fs.readFileSync(pagesFile, 'utf8');
    const pages = JSON.parse(raw);
    const page = pages.find(p => p.id === parseInt(req.params.id));

    if (!page) {
      return res.status(404).json({ error: 'Page not found' });
    }

    res.json(page);
  } catch (e) {
    res.status(500).json({ error: 'Cannot read page', detail: e.message });
  }
});

// Create new page
app.post('/api/pages', async (req, res) => {
  try {
    const raw = fs.readFileSync(pagesFile, 'utf8');
    const pages = JSON.parse(raw);

    const newPage = {
      id: Date.now(), // Simple ID generation
      title: req.body.title,
      slug: req.body.slug,
      content: req.body.content || '',
      status: req.body.status || 'draft',
      template: req.body.template || 'default',
      metaDescription: req.body.metaDescription || '',
      components: req.body.components || [],
      lastModified: new Date().toISOString()
    };

    // Check for duplicate slug
    if (pages.find(p => p.slug === newPage.slug)) {
      return res.status(400).json({ error: 'Page with this slug already exists' });
    }

    pages.push(newPage);
    fs.writeFileSync(pagesFile, JSON.stringify(pages, null, 2));

    // Generate HTML file for the page
    await generatePageFile(newPage);

    res.json(newPage);
  } catch (e) {
    res.status(500).json({ error: 'Cannot create page', detail: e.message });
  }
});

// Update page
app.put('/api/pages/:id', async (req, res) => {
  try {
    const raw = fs.readFileSync(pagesFile, 'utf8');
    const pages = JSON.parse(raw);
    const pageIndex = pages.findIndex(p => p.id === parseInt(req.params.id));

    if (pageIndex === -1) {
      return res.status(404).json({ error: 'Page not found' });
    }

    // Check for duplicate slug (excluding current page)
    const existingPage = pages.find(p => p.slug === req.body.slug && p.id !== parseInt(req.params.id));
    if (existingPage) {
      return res.status(400).json({ error: 'Page with this slug already exists' });
    }

    pages[pageIndex] = {
      ...pages[pageIndex],
      title: req.body.title,
      slug: req.body.slug,
      content: req.body.content,
      status: req.body.status,
      template: req.body.template,
      metaDescription: req.body.metaDescription,
      components: req.body.components || [],
      lastModified: new Date().toISOString()
    };

    fs.writeFileSync(pagesFile, JSON.stringify(pages, null, 2));

    // Regenerate HTML file for the updated page
    await generatePageFile(pages[pageIndex]);

    res.json(pages[pageIndex]);
  } catch (e) {
    res.status(500).json({ error: 'Cannot update page', detail: e.message });
  }
});

// Delete page
app.delete('/api/pages/:id', async (req, res) => {
  try {
    const raw = fs.readFileSync(pagesFile, 'utf8');
    const pages = JSON.parse(raw);
    const pageIndex = pages.findIndex(p => p.id === parseInt(req.params.id));

    if (pageIndex === -1) {
      return res.status(404).json({ error: 'Page not found' });
    }

    const deletedPage = pages.splice(pageIndex, 1)[0];
    fs.writeFileSync(pagesFile, JSON.stringify(pages, null, 2));

    res.json({ success: true, message: 'Page deleted successfully', page: deletedPage });
  } catch (e) {
    res.status(500).json({ error: 'Cannot delete page', detail: e.message });
  }
});

// Serve generated pages
app.get('/pages/:slug', (req, res) => {
  try {
    const pagePath = path.join(__dirname, 'pages', req.params.slug + '.html');
    if (fs.existsSync(pagePath)) {
      res.sendFile(pagePath);
    } else {
      res.status(404).send('Page not found');
    }
  } catch (error) {
    res.status(500).send('Error loading page');
  }
});

// SEO Settings API
const seoFile = path.join(__dirname, 'data', 'seo.json');

// Ensure seo.json exists
if (!fs.existsSync(seoFile)) {
  const defaultSEO = {
    metaDescription: '',
    metaKeywords: '',
    googleAnalytics: '',
    robots: 'index,follow',
    sitemap: true
  };
  fs.writeFileSync(seoFile, JSON.stringify(defaultSEO, null, 2));
}

// Get SEO settings
app.get('/api/seo', async (req, res) => {
  try {
    const raw = fs.readFileSync(seoFile, 'utf8');
    const seo = JSON.parse(raw);
    res.json(seo);
  } catch (e) {
    res.status(500).json({ error: 'Cannot read SEO settings', detail: e.message });
  }
});

// Update SEO settings
app.post('/api/seo', async (req, res) => {
  try {
    fs.writeFileSync(seoFile, JSON.stringify(req.body, null, 2));
    res.json({ success: true, message: 'SEO settings updated successfully' });
  } catch (e) {
    res.status(500).json({ error: 'Cannot update SEO settings', detail: e.message });
  }
});

// Export data API
app.get('/api/export', async (req, res) => {
  try {
    const exportData = {
      content: {},
      pages: [],
      seo: {},
      exportDate: new Date().toISOString()
    };

    // Export content
    if (fs.existsSync(dataFile)) {
      const contentRaw = fs.readFileSync(dataFile, 'utf8');
      exportData.content = JSON.parse(contentRaw);
    }

    // Export pages
    if (fs.existsSync(pagesFile)) {
      const pagesRaw = fs.readFileSync(pagesFile, 'utf8');
      exportData.pages = JSON.parse(pagesRaw);
    }

    // Export SEO
    if (fs.existsSync(seoFile)) {
      const seoRaw = fs.readFileSync(seoFile, 'utf8');
      exportData.seo = JSON.parse(seoRaw);
    }

    res.setHeader('Content-Type', 'application/json');
    res.setHeader('Content-Disposition', 'attachment; filename="website-backup-' + Date.now() + '.json"');
    res.send(JSON.stringify(exportData, null, 2));
  } catch (e) {
    res.status(500).json({ error: 'Export failed', detail: e.message });
  }
});

// Error handling middleware
app.use((err, req, res, next) => {
  if (err instanceof multer.MulterError) {
    if (err.code === 'LIMIT_FILE_SIZE') {
      return res.status(400).json({ error: 'File too large. Maximum size is 10MB.' });
    }
  }

  if (err.message.includes('Only image files')) {
    return res.status(400).json({ error: err.message });
  }

  console.error('Server error:', err);
  res.status(500).json({ error: 'Internal server error', detail: err.message });
});

app.listen(port, () => console.log('Server running on http://localhost:' + port));
