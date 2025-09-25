const path = require('path');
const db = require('../db');

async function run() {
  if (!db.configured()) {
    console.error('MySQL not configured. Set MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, MYSQL_HOST.');
    process.exit(1);
  }
  try {
    await db.init();
    const file = path.join(__dirname, '..', 'data', 'content.json');
    await db.migrateFromFile(file);
    console.log('Migration complete.');
    process.exit(0);
  } catch (e) {
    console.error('Migration failed:', e);
    process.exit(2);
  }
}

run();
