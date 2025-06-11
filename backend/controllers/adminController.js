const { User, Complaint, Category } = require("../models");
const { Op } = require("sequelize");

class AdminController {
  // Get all users
  static async getAllUsers(req, res) {
    try {
      const { role } = req.query;

      const whereClause = {};
      if (role) {
        whereClause.role = role;
      }

      const users = await User.findAll({
        where: whereClause,
        attributes: { exclude: ["password"] },
        order: [["created_at", "DESC"]],
      });

      res.json({
        status: "success",
        data: {
          users: users,
          total_users: users.length,
        },
      });
    } catch (error) {
      console.error("Get all users error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Delete user (cannot delete admin)
  static async deleteUser(req, res) {
    try {
      const userId = req.params.id;

      // Check if user exists and is not admin
      const user = await User.findByPk(userId);

      if (!user) {
        return res.status(404).json({
          status: "error",
          message: "User not found",
          code: "USER_NOT_FOUND",
        });
      }

      if (user.role === "admin") {
        return res.status(403).json({
          status: "error",
          message: "Cannot delete admin users",
          code: "CANNOT_DELETE_ADMIN",
        });
      }

      // Delete user
      const deletedRows = await User.destroy({
        where: {
          id: userId,
          role: { [Op.ne]: "admin" },
        },
      });

      if (deletedRows === 0) {
        return res.status(403).json({
          status: "error",
          message: "Cannot delete this user",
          code: "DELETE_FORBIDDEN",
        });
      }

      res.json({
        status: "success",
        message: "User deleted successfully",
      });
    } catch (error) {
      console.error("Delete user error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Update user status (active/inactive)
  static async updateUserStatus(req, res) {
    try {
      const userId = req.params.id;
      const { is_active } = req.body;

      // Validate is_active is a boolean
      if (typeof is_active !== "boolean") {
        return res.status(400).json({
          status: "error",
          message: "is_active must be a boolean",
          code: "INVALID_INPUT",
        });
      }

      const user = await User.findByPk(userId);

      if (!user) {
        return res.status(404).json({
          status: "error",
          message: "User not found",
          code: "USER_NOT_FOUND",
        });
      }

      if (user.role === "admin") {
        return res.status(403).json({
          status: "error",
          message: "Cannot update admin status",
          code: "CANNOT_UPDATE_ADMIN",
        });
      }

      await User.update(
        { is_active: is_active },
        {
          where: {
            id: userId,
          },
        }
      );

      res.json({
        status: "success",
        message: "User status updated successfully",
      });
    } catch (error) {
      console.error("Update user status error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Get admin dashboard statistics
  static async getDashboard(req, res) {
    try {
      // Get total complaints
      const totalComplaints = await Complaint.count();

      // Get complaints by status
      const complaintsByStatus = await Complaint.findAll({
        attributes: ["status", [Category.sequelize.fn("COUNT", Category.sequelize.col("status")), "count"]],
        group: ["status"],
        raw: true,
      });

      // Get complaints by category
      const complaintsByCategory = await Complaint.findAll({
        attributes: [
          [Category.sequelize.col("category.name"), "category"],
          [Category.sequelize.fn("COUNT", Category.sequelize.col("Complaint.id")), "count"],
        ],
        include: [
          {
            model: Category,
            as: "category",
            attributes: [],
          },
        ],
        group: ["category.name"],
        raw: true,
      });

      // Get total users (students only)
      const totalUsers = await User.count({
        where: { role: "mahasiswa" },
      });

      // Get recent complaints
      const recentComplaints = await Complaint.findAll({
        attributes: ["id", "title", "status", "date_posted"],
        include: [
          {
            model: Category,
            as: "category",
            attributes: ["name"],
          },
        ],
        order: [["date_posted", "DESC"]],
        limit: 5,
      });

      // Format data
      const statusData = {};
      complaintsByStatus.forEach((row) => {
        statusData[row.status] = row.count;
      });

      const categoryData = {};
      complaintsByCategory.forEach((row) => {
        categoryData[row.category] = row.count;
      });

      res.json({
        status: "success",
        data: {
          total_complaints: totalComplaints,
          complaints_by_status: statusData,
          complaints_by_category: categoryData,
          total_users: totalUsers,
          recent_complaints: recentComplaints,
        },
      });
    } catch (error) {
      console.error("Admin dashboard error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }
}

module.exports = AdminController;
