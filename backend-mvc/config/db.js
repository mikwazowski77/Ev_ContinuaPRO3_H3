const mysql = require('mysql2/promise');

const pool = mysql.createPool({
    host: 'localhost',
    port: 3310,
    user: 'user_miguel',
    password: 'passmiguel123',
    database: 'db_minimarket_pro413',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

module.exports = pool;