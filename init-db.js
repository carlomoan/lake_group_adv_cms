#!/usr/bin/env node

/**
 * Database Initialization Script
 * Creates database tables and migrates existing content from JSON files
 */

const db = require('./db');
const fs = require('fs');
const path = require('path');

async function initializeDatabase() {
    console.log('🔄 Initializing database...');

    try {
        // Test database connection
        const isConnected = await db.testConnection();
        if (!isConnected) {
            console.error('❌ Database connection failed. Please check your .env configuration.');
            process.exit(1);
        }

        console.log('✅ Database connection successful');

        // Initialize tables
        await db.init();
        console.log('✅ Database tables created successfully');

        // Check if we need to migrate content from JSON files
        const contentFile = path.join(__dirname, 'data', 'content.json');
        if (fs.existsSync(contentFile)) {
            console.log('🔄 Migrating content from JSON file to database...');

            try {
                await db.migrateFromFile(contentFile);
                console.log('✅ Content migration completed successfully');

                // Optionally backup the original file
                const backupFile = contentFile + '.backup.' + Date.now();
                fs.copyFileSync(contentFile, backupFile);
                console.log(`📦 Original content backed up to: ${backupFile}`);

            } catch (error) {
                console.warn('⚠️  Content migration failed:', error.message);
                console.log('💡 Content will be served from file until migration succeeds');
            }
        }

        // Show database statistics
        const stats = await db.getStats();
        console.log('\n📊 Database Statistics:');
        console.log(`   Content Items: ${stats.contentItems || 0}`);
        console.log(`   Media Files: ${stats.mediaFiles || 0}`);
        console.log(`   Published Pages: ${stats.publishedPages || 0}`);
        console.log(`   Total Storage: ${stats.totalStorageMB || 0} MB`);

        console.log('\n🎉 Database initialization completed successfully!');
        console.log('\n💡 Next steps:');
        console.log('   1. Start the server: npm start');
        console.log('   2. Visit admin panel: http://localhost:3001/admin');
        console.log('   3. Username: admin, Password: changeme (configure in .env)');

    } catch (error) {
        console.error('❌ Database initialization failed:', error.message);
        console.error('\n🔧 Troubleshooting tips:');
        console.error('   1. Make sure MySQL server is running');
        console.error('   2. Check database credentials in .env file');
        console.error('   3. Ensure database exists and user has proper permissions');
        console.error('   4. Try: CREATE DATABASE lake_db; (or your configured database name)');
        process.exit(1);
    } finally {
        await db.close();
    }
}

// Run if called directly
if (require.main === module) {
    initializeDatabase();
}

module.exports = { initializeDatabase };