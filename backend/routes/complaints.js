const express = require("express");
const { body } = require("express-validator");
const ComplaintController = require("../controllers/complaintController");
const { authenticateToken, requireAdmin } = require("../middleware/auth");
const upload = require("../middleware/upload");
const { Category } = require("../models");
const sequelize = require("sequelize");
const router = express.Router();

// Optional authentication middleware (allows both authenticated and anonymous users)
const optionalAuth = async (req, res, next) => {
  const authHeader = req.headers["authorization"];
  const token = authHeader && authHeader.split(" ")[1];

  if (token) {
    try {
      const jwt = require("jsonwebtoken");
      const { User } = require("../models");

      const decoded = jwt.verify(token, process.env.JWT_SECRET);
      const user = await User.findByPk(decoded.userId, {
        attributes: { exclude: ["password"] },
      });

      if (user) {
        req.user = user;
      }
    } catch (error) {
      // Silently continue without user if token is invalid
      console.log("Optional auth failed:", error.message);
    }
  }

  next();
};

// Dynamic category validation middleware
const validateCategory = async (req, res, next) => {
  let categoryName = req.body.category;
  if (!categoryName) {
    return res.status(422).json({
      status: "error",
      message: "Category is required",
      code: "CATEGORY_REQUIRED",
    });
  }
  // Normalisasi: trim dan lowercase
  categoryName = categoryName.trim().toLowerCase();
  try {
    // Cari kategori tanpa case-sensitive
    const category = await Category.findOne({
      where: {
        name: sequelize.where(sequelize.fn("LOWER", sequelize.col("name")), categoryName),
      },
    });
    if (!category) {
      return res.status(422).json({
        status: "error",
        message: "Invalid category",
        code: "INVALID_CATEGORY",
      });
    }
    // Simpan category_id ke req agar controller bisa pakai
    req.categoryObj = category;
    next();
  } catch (err) {
    return res.status(500).json({
      status: "error",
      message: "Internal server error",
      code: "SERVER_ERROR",
    });
  }
};

// Validation rules (remove static category validation)
const complaintValidation = [
  body("title").notEmpty().withMessage("Title is required"),
  body("description").notEmpty().withMessage("Description is required"),
  body("tanggal_kejadian").isDate().withMessage("Valid date is required"),
  body("lokasi_kejadian").notEmpty().withMessage("Location is required"),
  body("tipe_aduan").isIn(["private", "public"]).withMessage("Type must be private or public"),
];

// Routes
router.get("/", optionalAuth, ComplaintController.getComplaints);
router.post(
  "/",
  optionalAuth, // Use optional auth instead of required auth
  upload.single("lampiran"),
  complaintValidation,
  validateCategory,
  ComplaintController.submitComplaint
);
router.get("/:id", optionalAuth, ComplaintController.getComplaintById);
router.put("/:id/status", authenticateToken, requireAdmin, ComplaintController.updateComplaintStatus);
router.delete("/:id", authenticateToken, requireAdmin, ComplaintController.deleteComplaint);

module.exports = router;
