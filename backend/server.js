require("dotenv").config();

const express = require("express");
const cors = require("cors");
const helmet = require("helmet");
const path = require("path");

const app = express();

// Security middleware
app.use(
  helmet({
    crossOriginEmbedderPolicy: false,
    contentSecurityPolicy: false,
  })
);

// CORS configuration
app.use(
  cors({
    origin: function (origin, callback) {
      if (!origin) return callback(null, true);
      if (origin.includes("localhost") || origin.includes("127.0.0.1") || origin.includes("34.121.164.196")) {
        return callback(null, true);
      }
      const allowedOrigins = process.env.ALLOWED_ORIGINS ? process.env.ALLOWED_ORIGINS.split(",").map((origin) => origin.trim()) : [process.env.FRONTEND_URL || "http://localhost:5173"];
      if (allowedOrigins.indexOf(origin) !== -1) {
        return callback(null, true);
      }
      callback(null, true); // Allow all for development
    },
    credentials: true,
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization", "Accept", "Origin", "X-Requested-With"],
    optionsSuccessStatus: 200,
  })
);

// Body parsing middleware
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true, limit: "10mb" }));

// Static file serving with CORS headers
app.use(
  "/uploads",
  express.static(path.join(__dirname, "uploads"), {
    setHeaders: (res, path) => {
      res.setHeader("Access-Control-Allow-Origin", "*");
      res.setHeader("Access-Control-Allow-Methods", "GET");
      res.setHeader("Cross-Origin-Resource-Policy", "cross-origin");
    },
  })
);

// Health check endpoint
app.get("/api/health", async (req, res) => {
  try {
    // Import here to avoid circular dependency issues
    const { testConnection } = require("./config/database");
    const { testPgConnection } = require("./config/database");

    let mysqlStatus = false;
    let postgresStatus = false;

    try {
      mysqlStatus = await testConnection();
    } catch (error) {
      console.error("MySQL health check failed:", error.message);
    }

    try {
      postgresStatus = await testPgConnection();
    } catch (error) {
      console.error("PostgreSQL health check failed:", error.message);
    }

    res.json({
      status: "success",
      message: "Dual Server is running",
      timestamp: new Date().toISOString(),
      databases: {
        mysql: {
          status: mysqlStatus ? "connected" : "disconnected",
          role: "primary",
        },
        postgresql: {
          status: postgresStatus ? "connected" : "disconnected",
          role: "backup",
        },
      },
      port: process.env.PORT || 3000,
    });
  } catch (error) {
    console.error("Health check error:", error);
    res.status(500).json({
      status: "error",
      message: "Health check failed",
      error: error.message,
    });
  }
});

// Import and setup routes
try {
  const authRoutes = require("./routes/auth");
  const complaintRoutes = require("./routes/complaints");
  const userRoutes = require("./routes/users");
  const adminRoutes = require("./routes/admin");
  const categoryRoutes = require("./routes/categories");
  const fileRoutes = require("./routes/files");

  app.use("/api/auth", authRoutes);
  app.use("/api/complaints", complaintRoutes);
  app.use("/api/users", userRoutes);
  app.use("/api/admin", adminRoutes);
  app.use("/api/categories", categoryRoutes);
  app.use("/api/files", fileRoutes);
} catch (error) {
  console.error("❌ Error loading routes:", error.message);
}

// 404 handler
app.use("*", (req, res) => {
  res.status(404).json({
    status: "error",
    message: "Endpoint not found",
    code: "NOT_FOUND",
  });
});

// Global error handler
app.use((err, req, res, next) => {
  console.error("Error:", err);

  if (err.message === "Not allowed by CORS") {
    return res.status(403).json({
      status: "error",
      message: "CORS policy violation",
      code: "CORS_ERROR",
    });
  }

  if (err.name === "SequelizeValidationError") {
    return res.status(400).json({
      status: "error",
      message: "Validation error",
      errors: err.errors.map((e) => ({
        field: e.path,
        message: e.message,
      })),
    });
  }

  if (err.name === "SequelizeUniqueConstraintError") {
    return res.status(400).json({
      status: "error",
      message: "Data already exists",
      errors: err.errors.map((e) => ({
        field: e.path,
        message: `${e.path} already exists`,
      })),
    });
  }

  res.status(500).json({
    status: "error",
    message: "Internal server error",
    code: "INTERNAL_ERROR",
  });
});

// Start server with improved error handling
const startServer = async () => {
  try {
    console.log("🚀 Starting dual database server application...");

    // Import database connections
    const { testConnection, syncModels } = require("./models");
    const { testPgConnection, syncPgModels } = require("./models/postgres");

    let mysqlConnected = false;
    let postgresConnected = false;

    // Test MySQL connection
    try {
      mysqlConnected = await testConnection();
      if (mysqlConnected) {
        console.log("✅ MySQL database connected");
        await syncModels();
        console.log("✅ MySQL models synchronized");
      } else {
        console.error("❌ Failed to connect to MySQL database (primary)");
      }
    } catch (error) {
      console.error("❌ MySQL connection error:", error.message);
    }

    // Test PostgreSQL connection (optional)
    try {
      postgresConnected = await testPgConnection();
      if (postgresConnected) {
        console.log("✅ PostgreSQL database connected");
        await syncPgModels();
        console.log("✅ PostgreSQL models synchronized");

        // Initialize database sync only if both databases are connected
        if (mysqlConnected) {
          try {
            const { initializeDatabaseSync } = require("./services/databaseSync");
            await initializeDatabaseSync();
            console.log("✅ Database synchronization service started");
          } catch (syncError) {
            console.error("❌ Database sync service error:", syncError.message);
          }
        }
      } else {
        console.warn("⚠️  PostgreSQL backup database not available");
      }
    } catch (error) {
      console.warn("⚠️  PostgreSQL connection warning:", error.message);
    }

    const PORT = process.env.PORT || 3000;
    const server = app.listen(PORT, "0.0.0.0", () => {
      console.log(`🚀 Dual Database Server running on port ${PORT}`);
      console.log(`📊 Environment: ${process.env.NODE_ENV || "development"}`);
      console.log(`🔗 Server URL: http://localhost:${PORT}`);
      console.log(`🗄️  Primary DB: MySQL ${mysqlConnected ? "✅" : "❌"}`);
      console.log(`🗄️  Backup DB: PostgreSQL ${postgresConnected ? "✅" : "❌"}`);

      if (!mysqlConnected) {
        console.log("\n🔧 To fix MySQL connection:");
        console.log("1. Make sure XAMPP MySQL service is running");
        console.log("2. Database will be created automatically");
        console.log("3. Check your .env file configuration");
      }

      if (!postgresConnected) {
        console.log("\n🔧 PostgreSQL is optional for basic functionality");
        console.log("1. Install PostgreSQL if you want backup database");
        console.log("2. Update .env with correct PostgreSQL credentials");
      }
    });

    process.on("SIGTERM", () => {
      console.log("SIGTERM received, shutting down gracefully");
      server.close(() => {
        console.log("Process terminated");
      });
    });
  } catch (error) {
    console.error("❌ Failed to start server:", error);
    console.log("🔄 Server will continue with limited functionality...");
  }
};

startServer();

module.exports = app;
