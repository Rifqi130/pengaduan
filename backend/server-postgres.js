const express = require("express");
const cors = require("cors");
const helmet = require("helmet");
const morgan = require("morgan");
require("dotenv").config();

// Import PostgreSQL routes
const pgLogRoutes = require("./routes/postgres/logs");
const pgAnalyticsRoutes = require("./routes/postgres/analytics");

const app = express();

// Security middleware
app.use(helmet());

// CORS configuration
app.use(
  cors({
    origin: process.env.FRONTEND_URL || "http://localhost:5173",
    credentials: true,
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Content-Type", "Authorization"],
  })
);

// Logging middleware
if (process.env.NODE_ENV === "development") {
  app.use(morgan("dev"));
}

// Body parsing middleware
app.use(express.json({ limit: "10mb" }));
app.use(express.urlencoded({ extended: true, limit: "10mb" }));

// Health check endpoint
app.get("/api/health", (req, res) => {
  res.json({
    status: "success",
    message: "PostgreSQL Server is running",
    timestamp: new Date().toISOString(),
    server: "postgresql",
    port: process.env.PG_PORT || 3001,
  });
});

// API routes
app.use("/api/logs", pgLogRoutes);
app.use("/api/analytics", pgAnalyticsRoutes);

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
  console.error("PostgreSQL Error:", err);
  res.status(500).json({
    status: "error",
    message: "Internal server error",
    code: "INTERNAL_ERROR",
  });
});

const PORT = process.env.PG_PORT || 3001;
app.listen(PORT, () => {
  console.log(`🐘 PostgreSQL Server running on port ${PORT}`);
  console.log(`📊 Environment: ${process.env.NODE_ENV || "development"}`);
});

module.exports = app;
