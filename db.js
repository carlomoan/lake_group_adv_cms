const mysql = require('mysql2/promise');
require('dotenv').config();

class Database {
    constructor() {
        this.pool = null;
        this.configured = false;
        this.initPool();
    }

    initPool() {
        try {
            const config = {
                host: process.env.MYSQL_HOST || '127.0.0.1',
                port: process.env.MYSQL_PORT || 3306,
                user: process.env.MYSQL_USER || 'root',
                password: process.env.MYSQL_PASSWORD || '123456',
                database: process.env.MYSQL_DATABASE || 'lake_db',
                waitForConnections: true,
                connectionLimit: 10,
                queueLimit: 0,
                acquireTimeout: 60000,
                timezone: 'Z',
                charset: 'utf8mb4',
            };

            this.pool = mysql.createPool(config);
            this.configured = true;
            console.log('✅ Database pool initialized successfully');
        } catch (error) {
            console.warn('❌ Database configuration failed:', error.message);
            this.configured = false;
        }
    }

    async testConnection() {
        if (!this.configured || !this.pool) return false;

        try {
            const connection = await this.pool.getConnection();
            await connection.ping();
            connection.release();
            return true;
        } catch (error) {
            console.warn('Database connection test failed:', error.message);
            return false;
        }
    }

    isConfigured() {
        return this.configured && this.pool !== null;
    }

    async init() {
        if (!this.isConfigured()) {
            throw new Error('Database not configured');
        }

        // Create tables if they don't exist
        await this.createTables();
        console.log('✅ Database tables initialized');
    }

    async createTables() {
        const tables = [
            // Site Settings Table
            `CREATE TABLE IF NOT EXISTS site_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                site_title VARCHAR(255) DEFAULT 'Lake Group - Eastern & Central Africa',
                tagline VARCHAR(255),
                favicon_url VARCHAR(500),
                logo_main VARCHAR(500),
                logo_transparent VARCHAR(500),
                logo_width INT DEFAULT 150,
                logo_height INT DEFAULT 50,
                logo_alt_text VARCHAR(255),
                primary_color VARCHAR(7) DEFAULT '#FFD200',
                secondary_color VARCHAR(7) DEFAULT '#484939',
                tertiary_color VARCHAR(7) DEFAULT '#1E3A8A',
                navbar_bg_color VARCHAR(7),
                footer_text TEXT,
                footer_bg_image VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )`,

            // Content Storage Table (JSON-based for flexibility)
            `CREATE TABLE IF NOT EXISTS content_storage (
                id INT PRIMARY KEY AUTO_INCREMENT,
                content_type VARCHAR(50) NOT NULL,
                content_key VARCHAR(100) NOT NULL,
                content_data JSON,
                is_active BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_content (content_type, content_key)
            )`,

            // Media Library Table
            `CREATE TABLE IF NOT EXISTS media_library (
                id INT PRIMARY KEY AUTO_INCREMENT,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_url VARCHAR(500) NOT NULL,
                file_type VARCHAR(50) NOT NULL,
                file_size INT NOT NULL,
                alt_text VARCHAR(255),
                caption TEXT,
                mime_type VARCHAR(100) NOT NULL,
                width INT,
                height INT,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )`,

            // Pages Table
            `CREATE TABLE IF NOT EXISTS pages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT,
                status ENUM('draft', 'published', 'private') DEFAULT 'draft',
                template VARCHAR(100) DEFAULT 'default',
                meta_description TEXT,
                components JSON,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )`,

            // SEO Settings Table
            `CREATE TABLE IF NOT EXISTS seo_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                meta_title VARCHAR(255),
                meta_description TEXT,
                meta_keywords TEXT,
                google_analytics_id VARCHAR(50),
                google_search_console VARCHAR(255),
                facebook_pixel VARCHAR(50),
                robots_txt TEXT,
                custom_head_code TEXT,
                custom_body_code TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )`
        ];

        for (const tableSQL of tables) {
            try {
                await this.pool.execute(tableSQL);
            } catch (error) {
                console.error('Error creating table:', error.message);
                throw error;
            }
        }
    }

    async getContent() {
        if (!this.isConfigured()) return null;

        try {
            const [rows] = await this.pool.execute(
                'SELECT content_key, content_data FROM content_storage WHERE content_type = ? AND is_active = 1',
                ['main_content']
            );

            if (rows.length === 0) return null;

            // Combine all content pieces into a single object
            const content = {};
            rows.forEach(row => {
                content[row.content_key] = row.content_data;
            });

            return content;
        } catch (error) {
            console.error('Error getting content from database:', error);
            return null;
        }
    }

    // Get content specifically for public API (returns processed data)
    async getPublicContent() {
        if (!this.isConfigured()) return null;

        try {
            const content = await this.getContent();
            if (!content) return null;

            // Process content for public consumption
            // Ensure proper data structure for Vue.js frontend
            if (content.hero && content.hero.slides) {
                // Ensure hero slides have proper structure
                content.hero.slides = content.hero.slides.map(slide => ({
                    ...slide,
                    buttons: slide.buttons || []
                }));
            }

            if (content.siteSettings) {
                // Ensure navbar settings have proper structure
                if (!content.siteSettings.navbar) {
                    content.siteSettings.navbar = {
                        backgroundColor: '',
                        position: 'fixed',
                        transparency: 0,
                        height: 70,
                        dropdown: {
                            backgroundColor: '#ffffff',
                            backgroundType: 'color',
                            backgroundImage: '',
                            textColor: '#333333'
                        }
                    };
                }
            }

            return content;
        } catch (error) {
            console.error('Error getting public content:', error);
            return null;
        }
    }

    async saveContent(contentData) {
        if (!this.isConfigured()) throw new Error('Database not configured');

        const connection = await this.pool.getConnection();

        try {
            await connection.beginTransaction();

            // Clear existing content
            await connection.execute(
                'DELETE FROM content_storage WHERE content_type = ?',
                ['main_content']
            );

            // Insert new content pieces
            const insertSQL = `
                INSERT INTO content_storage (content_type, content_key, content_data, is_active)
                VALUES (?, ?, ?, 1)
            `;

            for (const [key, value] of Object.entries(contentData)) {
                await connection.execute(insertSQL, ['main_content', key, JSON.stringify(value)]);
            }

            await connection.commit();
            console.log('✅ Content saved to database successfully');
        } catch (error) {
            await connection.rollback();
            console.error('Error saving content to database:', error);
            throw error;
        } finally {
            connection.release();
        }
    }

    async saveMediaFile(mediaData) {
        if (!this.isConfigured()) return false;

        try {
            const insertSQL = `
                INSERT INTO media_library
                (filename, original_name, file_path, file_url, file_type, file_size, mime_type, uploaded_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            `;

            const fileType = mediaData.originalName.split('.').pop().toLowerCase();
            const fileUrl = mediaData.path.startsWith('/') ? mediaData.path : '/' + mediaData.path;

            await this.pool.execute(insertSQL, [
                mediaData.filename,
                mediaData.originalName,
                mediaData.path,
                fileUrl,
                fileType,
                mediaData.size,
                mediaData.mimeType
            ]);

            console.log('✅ Media file saved to database:', mediaData.filename);
            return true;
        } catch (error) {
            console.error('Error saving media file to database:', error);
            return false;
        }
    }

    async getMediaFiles(limit = 50, offset = 0) {
        if (!this.isConfigured()) return [];

        try {
            const [rows] = await this.pool.execute(
                'SELECT * FROM media_library ORDER BY uploaded_at DESC LIMIT ? OFFSET ?',
                [limit, offset]
            );

            return rows.map(row => ({
                id: row.id,
                name: row.original_name,
                filename: row.filename,
                url: row.file_url,
                path: row.file_path,
                type: row.file_type,
                size: row.file_size,
                mimeType: row.mime_type,
                altText: row.alt_text,
                caption: row.caption,
                uploadDate: row.uploaded_at
            }));
        } catch (error) {
            console.error('Error getting media files from database:', error);
            return [];
        }
    }

    async deleteMediaFile(filename) {
        if (!this.isConfigured()) return false;

        try {
            await this.pool.execute(
                'DELETE FROM media_library WHERE filename = ?',
                [filename]
            );
            return true;
        } catch (error) {
            console.error('Error deleting media file from database:', error);
            return false;
        }
    }

    async getStats() {
        if (!this.isConfigured()) return {};

        try {
            const stats = {};

            // Get content count
            const [contentRows] = await this.pool.execute(
                'SELECT COUNT(*) as count FROM content_storage WHERE is_active = 1'
            );
            stats.contentItems = contentRows[0].count;

            // Get media count
            const [mediaRows] = await this.pool.execute(
                'SELECT COUNT(*) as count FROM media_library'
            );
            stats.mediaFiles = mediaRows[0].count;

            // Get pages count
            const [pagesRows] = await this.pool.execute(
                'SELECT COUNT(*) as count FROM pages WHERE status = "published"'
            );
            stats.publishedPages = pagesRows[0].count;

            // Get total storage size
            const [storageRows] = await this.pool.execute(
                'SELECT SUM(file_size) as total FROM media_library'
            );
            stats.totalStorageBytes = storageRows[0].total || 0;
            stats.totalStorageMB = Math.round(stats.totalStorageBytes / 1024 / 1024 * 100) / 100;

            return stats;
        } catch (error) {
            console.error('Error getting database stats:', error);
            return {};
        }
    }

    async migrateFromFile(filePath) {
        const fs = require('fs');

        if (!fs.existsSync(filePath)) {
            throw new Error('Source file does not exist: ' + filePath);
        }

        try {
            const fileContent = fs.readFileSync(filePath, 'utf8');
            const contentData = JSON.parse(fileContent);

            await this.saveContent(contentData);
            console.log('✅ Content migrated from file to database successfully');
        } catch (error) {
            console.error('Migration error:', error);
            throw error;
        }
    }

    async savePage(pageData) {
        if (!this.isConfigured()) throw new Error('Database not configured');

        try {
            const insertSQL = `
                INSERT INTO pages (title, slug, content, status, template, meta_description, components)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            `;

            const [result] = await this.pool.execute(insertSQL, [
                pageData.title,
                pageData.slug,
                pageData.content || '',
                pageData.status || 'draft',
                pageData.template || 'default',
                pageData.metaDescription || '',
                JSON.stringify(pageData.components || [])
            ]);

            return result.insertId;
        } catch (error) {
            console.error('Error saving page:', error);
            throw error;
        }
    }

    async updatePage(pageId, pageData) {
        if (!this.isConfigured()) throw new Error('Database not configured');

        try {
            const updateSQL = `
                UPDATE pages
                SET title = ?, slug = ?, content = ?, status = ?, template = ?,
                    meta_description = ?, components = ?, updated_at = NOW()
                WHERE id = ?
            `;

            await this.pool.execute(updateSQL, [
                pageData.title,
                pageData.slug,
                pageData.content || '',
                pageData.status || 'draft',
                pageData.template || 'default',
                pageData.metaDescription || '',
                JSON.stringify(pageData.components || []),
                pageId
            ]);

            return true;
        } catch (error) {
            console.error('Error updating page:', error);
            throw error;
        }
    }

    async getPages() {
        if (!this.isConfigured()) return [];

        try {
            const [rows] = await this.pool.execute(
                'SELECT * FROM pages ORDER BY created_at DESC'
            );

            return rows.map(row => ({
                ...row,
                components: row.components ? JSON.parse(row.components) : []
            }));
        } catch (error) {
            console.error('Error getting pages:', error);
            return [];
        }
    }

    async deletePage(pageId) {
        if (!this.isConfigured()) throw new Error('Database not configured');

        try {
            await this.pool.execute('DELETE FROM pages WHERE id = ?', [pageId]);
            return true;
        } catch (error) {
            console.error('Error deleting page:', error);
            throw error;
        }
    }

    async close() {
        if (this.pool) {
            await this.pool.end();
            console.log('✅ Database connection pool closed');
        }
    }
}

module.exports = new Database();