const express = require("express");
const path = require("path");
const fs = require("fs");
const router = express.Router();

// Route untuk mengakses file lampiran
router.get("/attachment/:filename", (req, res) => {
  try {
    const filename = req.params.filename;

    // Sanitize filename to prevent path traversal attacks
    const sanitizedFilename = path.basename(filename);
    const filePath = path.join(__dirname, "../uploads/complaints", sanitizedFilename);

    // Check if file exists
    if (!fs.existsSync(filePath)) {
      return res.status(404).json({
        status: "error",
        message: "File not found",
      });
    }

    // Get file stats
    const stats = fs.statSync(filePath);

    // Set appropriate headers
    const ext = path.extname(filename).toLowerCase();
    const mimeTypes = {
      ".jpg": "image/jpeg",
      ".jpeg": "image/jpeg",
      ".png": "image/png",
      ".gif": "image/gif",
      ".pdf": "application/pdf",
      ".doc": "application/msword",
      ".docx": "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    };

    const contentType = mimeTypes[ext] || "application/octet-stream";

    // Set CORS headers for file access
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Methods", "GET");
    res.setHeader("Content-Type", contentType);
    res.setHeader("Content-Length", stats.size);
    res.setHeader("Content-Disposition", `inline; filename="${sanitizedFilename}"`);
    res.setHeader("Cache-Control", "public, max-age=31536000"); // Cache for 1 year

    // Stream file
    const fileStream = fs.createReadStream(filePath);
    fileStream.on("error", (err) => {
      console.error("Error streaming file:", err);
      if (!res.headersSent) {
        res.status(500).json({
          status: "error",
          message: "Error reading file",
        });
      }
    });

    fileStream.pipe(res);
  } catch (error) {
    console.error("Error serving file:", error);
    res.status(500).json({
      status: "error",
      message: "Internal server error",
    });
  }
});

// Route untuk mendapatkan informasi file
router.get("/info/:filename", (req, res) => {
  try {
    const filename = req.params.filename;
    const sanitizedFilename = path.basename(filename);
    const filePath = path.join(__dirname, "../uploads/complaints", sanitizedFilename);

    if (!fs.existsSync(filePath)) {
      return res.status(404).json({
        status: "error",
        message: "File not found",
      });
    }

    const stats = fs.statSync(filePath);
    const ext = path.extname(filename).toLowerCase();

    res.json({
      status: "success",
      data: {
        filename: sanitizedFilename,
        size: stats.size,
        extension: ext,
        created: stats.birthtime,
        modified: stats.mtime,
      },
    });
  } catch (error) {
    console.error("Error getting file info:", error);
    res.status(500).json({
      status: "error",
      message: "Internal server error",
    });
  }
});

module.exports = router;
