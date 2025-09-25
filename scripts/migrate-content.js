const mysql = require('mysql2/promise');
const fs = require('fs').promises;
const path = require('path');
require('dotenv').config();

// Database configuration
const dbConfig = {
    host: process.env.MYSQL_HOST || '127.0.0.1',
    port: process.env.MYSQL_PORT || 3306,
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || '123456',
    database: process.env.MYSQL_DATABASE || 'lake_db',
    multipleStatements: true
};

async function createDatabase() {
    console.log('🔄 Setting up MySQL database...');

    try {
        // Connect without database to create it
        const connectionConfig = { ...dbConfig };
        delete connectionConfig.database;

        const connection = await mysql.createConnection(connectionConfig);

        // Create database
        await connection.execute(`CREATE DATABASE IF NOT EXISTS ${dbConfig.database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
        console.log(`✅ Database '${dbConfig.database}' created successfully`);

        await connection.end();
        return true;
    } catch (error) {
        console.error('❌ Error creating database:', error.message);
        return false;
    }
}

async function setupTables() {
    console.log('🔄 Setting up database tables...');

    try {
        const connection = await mysql.createConnection(dbConfig);

        // Create tables directly instead of using SQL file
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        `;

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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        `;

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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        `;

        const createNavigationTable = `
            CREATE TABLE IF NOT EXISTS navigation_menu (
                id INT PRIMARY KEY AUTO_INCREMENT,
                menu_id VARCHAR(50) NOT NULL,
                parent_id INT NULL,
                title VARCHAR(100) NOT NULL,
                url VARCHAR(255) NOT NULL,
                sort_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                has_submenu BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_menu_id (menu_id),
                INDEX idx_parent_id (parent_id),
                INDEX idx_sort_order (sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        `;

        // Execute table creation statements
        await connection.execute(createContentTable);
        await connection.execute(createSectionsTable);
        await connection.execute(createMediaTable);
        await connection.execute(createNavigationTable);

        console.log('✅ Database tables created successfully');
        await connection.end();
        return true;
    } catch (error) {
        console.error('❌ Error setting up tables:', error.message);
        return false;
    }
}

async function migrateContentFromFile() {
    console.log('🔄 Migrating content from JSON file to database...');

    try {
        const connection = await mysql.createConnection(dbConfig);

        // Read existing content.json
        const contentPath = path.join(__dirname, '../data/content.json');
        const contentData = await fs.readFile(contentPath, 'utf8');
        const content = JSON.parse(contentData);

        // Insert main content
        await connection.execute(
            'INSERT INTO site_content (content_key, content_data) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_data = VALUES(content_data), updated_at = CURRENT_TIMESTAMP',
            ['main_content', JSON.stringify(content)]
        );

        // Insert individual sections
        const sections = [
            { name: 'site_settings', type: 'general', data: content.siteSettings || {} },
            { name: 'navigation', type: 'navigation', data: content.navigation || {} },
            { name: 'hero_slider', type: 'hero', data: content.hero || {} },
            { name: 'services', type: 'services', data: content.services || {} },
            { name: 'about_section', type: 'about', data: content.aboutSection || {} },
            { name: 'features_section', type: 'features', data: content.featuresSection || {} },
            { name: 'companies', type: 'companies', data: content.companies || {} },
            { name: 'statistics', type: 'statistics', data: content.statistics || {} },
            { name: 'contact', type: 'contact', data: content.contact || {} }
        ];

        for (const section of sections) {
            await connection.execute(
                'INSERT INTO content_sections (section_name, section_type, content_data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE content_data = VALUES(content_data), updated_at = CURRENT_TIMESTAMP',
                [section.name, section.type, JSON.stringify(section.data)]
            );
        }

        console.log('✅ Content migrated successfully');
        await connection.end();
        return true;
    } catch (error) {
        console.error('❌ Error migrating content:', error.message);
        return false;
    }
}

async function testConnection() {
    console.log('🔄 Testing database connection...');

    try {
        const connection = await mysql.createConnection(dbConfig);

        // Test query
        const [rows] = await connection.execute('SELECT COUNT(*) as count FROM site_content');
        console.log(`✅ Database connection successful. Found ${rows[0].count} content records`);

        // Show tables
        const [tables] = await connection.execute('SHOW TABLES');
        console.log('📋 Available tables:', tables.map(t => Object.values(t)[0]).join(', '));

        await connection.end();
        return true;
    } catch (error) {
        console.error('❌ Database connection failed:', error.message);
        return false;
    }
}

async function main() {
    console.log('🚀 Starting Lake Group Database Setup...\n');

    // Step 1: Create database
    const dbCreated = await createDatabase();
    if (!dbCreated) {
        console.log('❌ Database setup failed. Please check your MySQL connection.');
        process.exit(1);
    }

    // Step 2: Setup tables
    const tablesCreated = await setupTables();
    if (!tablesCreated) {
        console.log('❌ Table setup failed.');
        process.exit(1);
    }

    // Step 3: Migrate existing content
    const contentMigrated = await migrateContentFromFile();
    if (!contentMigrated) {
        console.log('⚠️  Content migration failed, but database setup is complete.');
    }

    // Step 4: Test connection
    const connectionTest = await testConnection();
    if (!connectionTest) {
        console.log('⚠️  Connection test failed, but setup might still be successful.');
    }

    console.log('\n🎉 Database setup completed!');
    console.log('📝 Configuration:');
    console.log(`   Host: ${dbConfig.host}:${dbConfig.port}`);
    console.log(`   Database: ${dbConfig.database}`);
    console.log(`   User: ${dbConfig.user}`);
    console.log('\n💡 Your application should now use MySQL for content storage.');
}

// Run if called directly
if (require.main === module) {
    main().catch(error => {
        console.error('💥 Setup failed:', error.message);
        process.exit(1);
    });
}

module.exports = {
    createDatabase,
    setupTables,
    migrateContentFromFile,
    testConnection
};