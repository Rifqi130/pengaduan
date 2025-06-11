const { Category, PgLog } = require("../models");

class CategoryController {
  // Get all categories (MySQL) dan contoh log ke PostgreSQL
  static async getCategories(req, res) {
    try {
      // Simpan log ke PostgreSQL (opsional)
      await PgLog.create({ message: "Akses kategori" });

      const categories = await Category.findAll({
        where: { is_active: true },
        attributes: ["id", "name", "description"],
        order: [["name", "ASC"]],
      });

      res.json({
        status: "success",
        data: {
          categories: categories,
        },
      });
    } catch (error) {
      console.error("Get categories error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }
}

module.exports = CategoryController;
