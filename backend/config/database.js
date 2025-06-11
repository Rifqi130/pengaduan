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
    acquire: 30000,
    idle: 10000,
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
    acquire: 30000,
    idle: 10000,
  },
  timezone: "+07:00",
  define: {
    timestamps: true,
    underscored: false,
    freezeTableName: true,
  },
});

// Test database connection
const testConnection = async () => {
  try {
    await sequelize.authenticate();
    console.log("✅ Database connected successfully");
    return true;
  } catch (error) {
    console.error("❌ Database connection failed:", error.message);
    return false;
  }
};

// Test koneksi PostgreSQL
const testPgConnection = async () => {
  try {
    await sequelizePg.authenticate();
    console.log("✅ PostgreSQL connected successfully");
    return true;
  } catch (error) {
    console.error("❌ PostgreSQL connection failed:", error.message);
    return false;
  }
};

module.exports = {
  sequelize, // MySQL
  sequelizePg, // PostgreSQL
  testConnection, // Test MySQL
  testPgConnection, // Test PostgreSQL
};
