const mysql = require("mysql2/promise");
require("dotenv").config();

const createDatabase = async () => {
  const connection = await mysql.createConnection({
    host: process.env.DB_HOST || "localhost",
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USER || "root",
    password: process.env.DB_PASSWORD || "",
  });

  try {
    // Create database if it doesn't exist
    await connection.execute(`CREATE DATABASE IF NOT EXISTS \`${process.env.DB_NAME || "pengaduan_mahasiswa"}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
    console.log(`✅ Database '${process.env.DB_NAME || "pengaduan_mahasiswa"}' created or already exists`);
  } catch (error) {
    console.error("❌ Error creating database:", error.message);
    throw error;
  } finally {
    await connection.end();
  }
};

module.exports = { createDatabase };
