const { Sequelize } = require("sequelize");
require("dotenv").config();

// MySQL configuration - Fixed with better timeout handling
const sequelize = new Sequelize(process.env.DB_NAME || "pengaduan_mahasiswa", process.env.DB_USER || "root", process.env.DB_PASSWORD || "", {
  host: process.env.DB_HOST || "localhost",
  dialect: "mysql",
  port: process.env.DB_PORT || 3306,
  logging: process.env.NODE_ENV === "development" ? console.log : false,
  pool: {
    max: 5,
    min: 0,
    acquire: 60000, // Increased to 60 seconds
    idle: 10000,
  },
  dialectOptions: {
    connectTimeout: 60000, // 60 seconds
    acquireTimeout: 60000,
    timeout: 60000,
  },
  define: {
    charset: "utf8mb4",
  },
  retry: {
    match: [/ETIMEDOUT/, /EHOSTUNREACH/, /ECONNRESET/, /ECONNREFUSED/, /TIMEOUT/],
    max: 3,
  },
});

// PostgreSQL configuration - Fixed password handling
const sequelizePg = new Sequelize(process.env.PG_DB_NAME || "pengaduan_logs", process.env.PG_DB_USER || "postgres", process.env.PG_DB_PASSWORD || "", {
  host: process.env.PG_DB_HOST || "localhost",
  dialect: "postgres",
  port: process.env.PG_DB_PORT || 5432,
  logging: false,
  pool: {
    max: 5,
    min: 0,
    acquire: 30000,
    idle: 10000,
  },
});

// Test MySQL connection with better error handling
const testConnection = async () => {
  try {
    await sequelize.authenticate();
    console.log("✅ MySQL database connection has been established successfully.");
    return true;
  } catch (error) {
    console.error("❌ Unable to connect to MySQL database:", error.message);

    // If database doesn't exist, try to create it
    if (error.message.includes("Unknown database")) {
      console.log("🔄 Attempting to create MySQL database...");
      try {
        const { createDatabase } = require("../utils/createDatabase");
        await createDatabase();
        // Retry connection
        await sequelize.authenticate();
        console.log("✅ MySQL database created and connected successfully.");
        return true;
      } catch (createError) {
        console.error("❌ Failed to create database:", createError.message);
        return false;
      }
    }
    return false;
  }
};

// Test PostgreSQL connection with better error handling
const testPgConnection = async () => {
  try {
    await sequelizePg.authenticate();
    console.log("✅ PostgreSQL database connection has been established successfully.");
    return true;
  } catch (error) {
    console.warn("⚠️  PostgreSQL database connection failed:", error.message);
    return false;
  }
};

module.exports = {
  sequelize,
  sequelizePg,
  testConnection,
  testPgConnection,
};
