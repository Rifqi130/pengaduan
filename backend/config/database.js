const { Sequelize } = require("sequelize");
require("dotenv").config();

// Koneksi utama (MySQL)
const sequelize = new Sequelize({
  host: process.env.DB_HOST || "localhost",
  port: process.env.DB_PORT || 3306,
  database: process.env.DB_NAME || "pengaduan_mahasiswa",
  username: process.env.DB_USER || "root",
  password: process.env.DB_PASS || "",
  dialect: "mysql",
  charset: "utf8mb4",
  collate: "utf8mb4_unicode_ci",
  logging: process.env.NODE_ENV === "development" ? console.log : false,
  pool: {
    max: 10,
    min: 0,
    acquire: 60000, // Increased timeout for GCP
    idle: 10000,
  },
  dialectOptions: {
    connectTimeout: 60000, // 60 seconds
    acquireTimeout: 60000,
    timeout: 60000,
  },
  retry: {
    match: [/ETIMEDOUT/, /EHOSTUNREACH/, /ECONNRESET/, /ECONNREFUSED/, /ENOTFOUND/, /EAI_AGAIN/],
    max: 3,
  },
  timezone: "+07:00",
  define: {
    timestamps: true,
    underscored: false,
    freezeTableName: true,
  },
});

// Koneksi kedua (PostgreSQL)
const sequelizePg = new Sequelize(process.env.PG_DB_NAME || "pengaduan_mahasiswa", process.env.PG_DB_USER || "postgres", process.env.PG_DB_PASS || "", {
  host: process.env.PG_DB_HOST || "localhost",
  port: process.env.PG_DB_PORT || 5432,
  dialect: "postgres",
  logging: process.env.NODE_ENV === "development" ? console.log : false,
  pool: {
    max: 10,
    min: 0,
    acquire: 60000, // Increased timeout for GCP
    idle: 10000,
  },
  dialectOptions: {
    connectTimeout: 60000,
    requestTimeout: 60000,
  },
  retry: {
    match: [/ETIMEDOUT/, /EHOSTUNREACH/, /ECONNRESET/, /ECONNREFUSED/, /ENOTFOUND/, /EAI_AGAIN/],
    max: 3,
  },
  timezone: "+07:00",
  define: {
    timestamps: true,
    underscored: false,
    freezeTableName: true,
  },
});

// Test database connection with retry
const testConnection = async (retries = 3) => {
  for (let i = 0; i < retries; i++) {
    try {
      await sequelize.authenticate();
      console.log("✅ MySQL Database connected successfully");
      return true;
    } catch (error) {
      console.error(`❌ MySQL connection attempt ${i + 1} failed:`, error.message);
      if (i < retries - 1) {
        console.log(`⏳ Retrying in 5 seconds...`);
        await new Promise((resolve) => setTimeout(resolve, 5000));
      }
    }
  }
  return false;
};

// Test koneksi PostgreSQL with retry
const testPgConnection = async (retries = 3) => {
  for (let i = 0; i < retries; i++) {
    try {
      await sequelizePg.authenticate();
      console.log("✅ PostgreSQL connected successfully");
      return true;
    } catch (error) {
      console.error(`❌ PostgreSQL connection attempt ${i + 1} failed:`, error.message);
      if (i < retries - 1) {
        console.log(`⏳ Retrying in 5 seconds...`);
        await new Promise((resolve) => setTimeout(resolve, 5000));
      }
    }
  }
  return false;
};

module.exports = {
  sequelize, // MySQL
  sequelizePg, // PostgreSQL
  testConnection, // Test MySQL
  testPgConnection, // Test PostgreSQL
};
