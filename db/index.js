const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

const poolConfig = {
  host: process.env.MYSQL_HOST || '127.0.0.1',
  user: process.env.MYSQL_USER || 'root',
  password: process.env.MYSQL_PASSWORD || '123456',
  database: process.env.MYSQL_DATABASE || 'lake_db',
  port: process.env.MYSQL_PORT ? parseInt(process.env.MYSQL_PORT, 10) : 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  acquireTimeout: 60000,
  timeout: 60000
};

let pool = null;

function configured() {
  return !!(poolConfig.database && poolConfig.host && poolConfig.user);
}

async function init() {
  if (!configured()) return null;
  if (pool) return pool;

  try {
    pool = mysql.createPool(poolConfig);
    // Test connection
    const connection = await pool.getConnection();
    await connection.ping();
    connection.release();
    console.log('✅ MySQL connection pool initialized successfully');
    return pool;
  } catch (error) {
    console.error('❌ MySQL connection failed:', error.message);
    throw error;
  }
}

async function ensureTables() {
  const p = await init();
  if (!p) throw new Error('DB not configured');

  // Create main content table with new structure
  const createContentTable = `
    CREATE TABLE IF NOT EXISTS site_content (
      id INT PRIMARY KEY AUTO_INCREMENT,
      content_key VARCHAR(100) NOT NULL UNIQUE,
      content_data JSON NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      version INT DEFAULT 1,
      is_active BOOLEAN DEFAULT TRUE,
      INDEX idx_content_key (content_key),
      INDEX idx_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `;

  // Create content sections table
  const createSectionsTable = `
    CREATE TABLE IF NOT EXISTS content_sections (
      id INT PRIMARY KEY AUTO_INCREMENT,
      section_name VARCHAR(100) NOT NULL,
      section_type ENUM('general', 'navigation', 'hero', 'services', 'about', 'features', 'companies', 'statistics', 'contact') NOT NULL,
      content_data JSON NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      is_active BOOLEAN DEFAULT TRUE,
      INDEX idx_section_name (section_name),
      INDEX idx_section_type (section_type),
      INDEX idx_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `;

  // Create media files table
  const createMediaTable = `
    CREATE TABLE IF NOT EXISTS media_files (
      id INT PRIMARY KEY AUTO_INCREMENT,
      filename VARCHAR(255) NOT NULL,
      original_name VARCHAR(255) NOT NULL,
      file_path VARCHAR(500) NOT NULL,
      file_size INT NOT NULL,
      mime_type VARCHAR(100) NOT NULL,
      alt_text VARCHAR(255),
      uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      used_in JSON,
      is_active BOOLEAN DEFAULT TRUE,
      INDEX idx_filename (filename),
      INDEX idx_uploaded_at (uploaded_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `;

  await p.query(createContentTable);
  await p.query(createSectionsTable);
  await p.query(createMediaTable);
}

async function getContent() {
  const p = await init();
  if (!p) return null;

  try {
    await ensureTables();

    // Try to get main content first
    const [mainRows] = await p.query('SELECT content_data FROM site_content WHERE content_key = ? AND is_active = TRUE LIMIT 1', ['main_content']);

    if (mainRows && mainRows.length > 0) {
      return mainRows[0].content_data;
    }

    // If no main content, build from sections
    const [sectionRows] = await p.query('SELECT section_name, section_type, content_data FROM content_sections WHERE is_active = TRUE ORDER BY section_name');

    if (sectionRows && sectionRows.length > 0) {
      const content = {};
      for (const row of sectionRows) {
        // Map section names to content structure
        switch (row.section_name) {
          case 'site_settings':
            content.siteSettings = row.content_data;
            break;
          case 'navigation':
            content.navigation = row.content_data;
            break;
          case 'hero_slider':
            content.hero = row.content_data;
            break;
          case 'services':
          case 'services_section':
            content.services = row.content_data;
            break;
          case 'about_section':
            content.aboutSection = row.content_data;
            break;
          case 'features_section':
            content.featuresSection = row.content_data;
            break;
          case 'companies':
            content.companies = row.content_data;
            break;
          case 'statistics':
            content.statistics = row.content_data;
            break;
          case 'contact':
            content.contact = row.content_data;
            break;
          default:
            content[row.section_name] = row.content_data;
        }
      }

      // Set default site title if not present
      if (!content.siteTitle && content.siteSettings?.siteTitle) {
        content.siteTitle = content.siteSettings.siteTitle;
      } else if (!content.siteTitle) {
        content.siteTitle = 'Lake Group - Eastern & Central Africa';
      }

      return content;
    }

    return null;
  } catch (error) {
    console.error('Error getting content from database:', error);
    throw error;
  }
}

async function saveContent(obj) {
  const p = await init();
  if (!p) throw new Error('DB not configured');

  try {
    await ensureTables();

    // Start transaction
    const connection = await p.getConnection();
    await connection.beginTransaction();

    try {
      // Save main content
      const dataStr = JSON.stringify(obj);
      await connection.query(
        'INSERT INTO site_content (content_key, content_data) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_data = VALUES(content_data), updated_at = CURRENT_TIMESTAMP, version = version + 1',
        ['main_content', dataStr]
      );

      // Save individual sections
      const sections = [
        { name: 'site_settings', type: 'general', data: obj.siteSettings || {} },
        { name: 'navigation', type: 'navigation', data: obj.navigation || {} },
        { name: 'hero_slider', type: 'hero', data: obj.hero || {} },
        { name: 'services', type: 'services', data: obj.services || {} },
        { name: 'about_section', type: 'about', data: obj.aboutSection || {} },
        { name: 'features_section', type: 'features', data: obj.featuresSection || {} },
        { name: 'companies', type: 'companies', data: obj.companies || {} },
        { name: 'statistics', type: 'statistics', data: obj.statistics || {} },
        { name: 'contact', type: 'contact', data: obj.contact || {} }
      ];

      for (const section of sections) {
        await connection.query(
          'INSERT INTO content_sections (section_name, section_type, content_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content_data = VALUES(content_data), updated_at = CURRENT_TIMESTAMP',
          [section.name, section.type, JSON.stringify(section.data)]
        );
      }

      await connection.commit();
      connection.release();

      console.log('✅ Content saved to database successfully');
    } catch (error) {
      await connection.rollback();
      connection.release();
      throw error;
    }
  } catch (error) {
    console.error('Error saving content to database:', error);
    throw error;
  }
}

async function saveMediaFile(fileInfo) {
  const p = await init();
  if (!p) throw new Error('DB not configured');

  try {
    await ensureTables();

    await p.query(
      'INSERT INTO media_files (filename, original_name, file_path, file_size, mime_type, alt_text) VALUES (?, ?, ?, ?, ?, ?)',
      [fileInfo.filename, fileInfo.originalName, fileInfo.path, fileInfo.size, fileInfo.mimeType, fileInfo.altText || null]
    );

    console.log('✅ Media file saved to database');
  } catch (error) {
    console.error('Error saving media file to database:', error);
    throw error;
  }
}

async function getMediaFiles(limit = 50, offset = 0) {
  const p = await init();
  if (!p) return [];

  try {
    await ensureTables();

    const [rows] = await p.query(
      'SELECT * FROM media_files WHERE is_active = TRUE ORDER BY uploaded_at DESC LIMIT ? OFFSET ?',
      [limit, offset]
    );

    return rows;
  } catch (error) {
    console.error('Error getting media files from database:', error);
    return [];
  }
}

async function migrateFromFile(filePath) {
  const p = await init();
  if (!p) throw new Error('DB not configured');

  try {
    const raw = fs.readFileSync(filePath, 'utf8');
    const obj = JSON.parse(raw);
    await saveContent(obj);
    console.log('✅ Content migrated from file to database');
  } catch (error) {
    console.error('Error migrating content from file:', error);
    throw error;
  }
}

async function getStats() {
  const p = await init();
  if (!p) return null;

  try {
    await ensureTables();

    const [contentRows] = await p.query('SELECT COUNT(*) as content_count FROM site_content WHERE is_active = TRUE');
    const [sectionsRows] = await p.query('SELECT COUNT(*) as sections_count FROM content_sections WHERE is_active = TRUE');
    const [mediaRows] = await p.query('SELECT COUNT(*) as media_count FROM media_files WHERE is_active = TRUE');

    return {
      content_records: contentRows[0].content_count,
      content_sections: sectionsRows[0].sections_count,
      media_files: mediaRows[0].media_count,
      database: poolConfig.database,
      host: poolConfig.host
    };
  } catch (error) {
    console.error('Error getting database stats:', error);
    return null;
  }
}

// Close pool on process termination
process.on('SIGINT', async () => {
  if (pool) {
    await pool.end();
    console.log('🔌 MySQL connection pool closed');
  }
  process.exit(0);
});

module.exports = {
  configured,
  init,
  getContent,
  saveContent,
  saveMediaFile,
  getMediaFiles,
  migrateFromFile,
  getStats,
  ensureTables
};