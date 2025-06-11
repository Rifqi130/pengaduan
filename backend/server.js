require("dotenv").config();

const express = require("express");
const cors = require("cors");
const helmet = require("helmet");
// const morgan = require("morgan"); // Commented out temporarily
const path = require("path");

// Import database connections
const { testConnection, syncModels } = require("./models");
const { testPgConnection, syncPgModels } = require("./models/postgres");

// Import routes
const authRoutes = require("./routes/auth");
const complaintRoutes = require("./routes/complaints");
const userRoutes = require("./routes/users");
const adminRoutes = require("./routes/admin");
const categoryRoutes = require("./routes/categories");
const fileRoutes = require("./routes/files");

const app = express();

// Security middleware
app.use(helmet());

// CORS configuration
const allowedOrigins = process.env.ALLOWED_ORIGINS ? process.env.ALLOWED_ORIGINS.split(",").map((origin) => origin.trim()) : [process.env.FRONTEND_URL || "http://localhost:5173"];

app.use(
  cors({
    origin: function (origin, callback) {
      // Allow requests with no origin (like mobile apps or curl requests)
      if (!origin) return callback(null, true);

      if (allowedOrigins.indexOf(origin) !== -1) {
        callback(null, true);
      } else {
        console.log("Blocked by CORS:", origin);
        callback(new Error("Not allowed by CORS"));
      }
    },
    credentials: true,
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization"],
  })
);

// Logging middleware
// if (process.env.NODE_ENV === "development") {
//   app.use(morgan("dev"));
// }

// Body parsing middleware
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true, limit: "10mb" }));

// Static file serving
app.use("/uploads", express.static(path.join(__dirname, "uploads")));

// Health check endpoint
app.get("/api/health", (req, res) => {
  res.json({
    status: "success",
    message: "MySQL Server is running",
    timestamp: new Date().toISOString(),
    server: "mysql",
    port: process.env.PORT || 3000,
  });
});

// API routes
app.use("/api/auth", authRoutes);
app.use("/api/complaints", complaintRoutes);
app.use("/api/users", userRoutes);
app.use("/api/admin", adminRoutes);
app.use("/api/categories", categoryRoutes);
app.use("/api/files", fileRoutes);

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

  // Sequelize validation errors
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

  // Sequelize unique constraint errors
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

// Start MySQL server
const startMySQLServer = async () => {
  try {
    // Test database connection
    const isConnected = await testConnection();
    if (!isConnected) {
      console.error("❌ Failed to connect to MySQL database");
      process.exit(1);
    }

    // Sync models
    await syncModels();

    const PORT = process.env.PORT || 3000;
    app.listen(PORT, () => {
      console.log(`🚀 MySQL Server running on port ${PORT}`);
      console.log(`📊 Environment: ${process.env.NODE_ENV || "development"}`);
      console.log(`🔗 Frontend URL: ${process.env.FRONTEND_URL || "http://localhost:5173"}`);
    });
  } catch (error) {
    console.error("❌ Failed to start MySQL server:", error);
    process.exit(1);
  }
};

// Start PostgreSQL server
const startPostgreSQLServer = async () => {
  try {
    console.log("🔄 Starting PostgreSQL server...");
    const isConnected = await testPgConnection();
    if (!isConnected) {
      console.error("❌ Failed to connect to PostgreSQL database");
      return;
    }

    await syncPgModels();
    console.log("✅ PostgreSQL models synchronized");

    // Import and start PostgreSQL server
    require("./server-postgres");
  } catch (error) {
    console.error("❌ Failed to start PostgreSQL server:", error);
  }
};

// Start both servers
const startServers = async () => {
  console.log("🚀 Starting dual server application...");

  // Start MySQL server first
  await startMySQLServer();

  // Then start PostgreSQL server
  await startPostgreSQLServer();

  console.log("✅ Both servers started successfully");
};

startServers();

module.exports = app;
