const { Complaint, Category, User } = require("../models");
const { validationResult } = require("express-validator");
const { Op } = require("sequelize");
const moment = require("moment-timezone");

class ComplaintController {
  // Get complaints with filters
  static async getComplaints(req, res) {
    try {
      const { type, status, category } = req.query;

      const whereClause = {};

      // Apply filters
      if (type) {
        whereClause.tipe_aduan = type;
      }

      if (status) {
        whereClause.status = status;
      }

      // If not authenticated or not admin, only show public complaints
      if (!req.user) {
        whereClause.tipe_aduan = "public";
      }

      const includeClause = [
        {
          model: Category,
          as: "category",
          attributes: ["id", "name"],
          where: category ? { name: category } : undefined,
        },
        {
          model: User,
          as: "user",
          attributes: ["id", "username"],
          required: false,
        },
      ];

      const complaints = await Complaint.findAll({
        where: whereClause,
        include: includeClause,
        order: [["date_posted", "DESC"]],
      });

      // Convert date_posted to Asia/Jakarta (GMT+7)
      const complaintsWithLocalTime = complaints.map((c) => {
        const obj = c.toJSON();
        if (obj.date_posted) {
          obj.date_posted = moment(obj.date_posted).tz("Asia/Jakarta").format("YYYY-MM-DD HH:mm:ss");
        }
        return obj;
      });

      res.json({
        status: "success",
        data: {
          complaints: complaintsWithLocalTime,
          total: complaintsWithLocalTime.length,
        },
      });
    } catch (error) {
      console.error("Get complaints error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Submit new complaint
  static async submitComplaint(req, res) {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(422).json({
          status: "error",
          message: "Validation failed",
          errors: errors.mapped(),
        });
      }

      const { title, description, category, tanggal_kejadian, lokasi_kejadian, tipe_aduan, sertakan_data_diri, nama_pelapor, jenis_kelamin, nim, whatsapp, email_pelapor } = req.body;

      // Gunakan category dari middleware
      const categoryObj = req.categoryObj;

      // Handle both authenticated and anonymous submissions
      const userId = req.user ? req.user.id : null;

      // Jika user sudah login, cek status aktif
      if (req.user) {
        const { User } = require("../models");
        const user = await User.findByPk(req.user.id);

        if (!user) {
          return res.status(401).json({
            status: "error",
            message: "User not found",
            code: "USER_NOT_FOUND",
          });
        }

        if (!user.is_active) {
          return res.status(403).json({
            status: "error",
            message: "Akun Anda tidak aktif. Silakan hubungi admin.",
            code: "ACCOUNT_INACTIVE",
          });
        }
      }

      // Validasi data pribadi jika diperlukan
      const includePersonalData = sertakan_data_diri === true || sertakan_data_diri === "true";

      if (includePersonalData) {
        if (!nama_pelapor || nama_pelapor.trim() === "") {
          return res.status(422).json({
            status: "error",
            message: "Nama pelapor wajib diisi jika menyertakan data diri",
            code: "NAMA_REQUIRED",
          });
        }

        if (!email_pelapor || email_pelapor.trim() === "") {
          return res.status(422).json({
            status: "error",
            message: "Email pelapor wajib diisi jika menyertakan data diri",
            code: "EMAIL_REQUIRED",
          });
        }
      }

      const lampiran = req.file ? req.file.filename : null;
      const lampiranPath = req.file ? req.file.path : null;

      // Create complaint
      const complaint = await Complaint.create({
        user_id: userId,
        category_id: categoryObj.id,
        title,
        description,
        nama_pelapor: includePersonalData ? nama_pelapor : null,
        jenis_kelamin: includePersonalData ? jenis_kelamin : null,
        nim: includePersonalData ? nim : null,
        whatsapp: includePersonalData ? whatsapp : null,
        email_pelapor: includePersonalData ? email_pelapor : null,
        tanggal_kejadian,
        lokasi_kejadian,
        lampiran,
        lampiran_path: lampiranPath,
        tipe_aduan,
      });

      res.status(201).json({
        status: "success",
        message: "Complaint submitted successfully",
        data: {
          complaint_id: complaint.id,
          title: complaint.title,
          status: complaint.status,
          date_posted: complaint.date_posted,
        },
      });
    } catch (error) {
      console.error("Submit complaint error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Get specific complaint
  static async getComplaintById(req, res) {
    try {
      const complaintId = req.params.id;

      const whereClause = { id: complaintId };

      // Access control: user can only see their own private complaints or public complaints
      if (req.user && req.user.role !== "admin") {
        whereClause[Op.or] = [{ user_id: req.user.id }, { tipe_aduan: "public" }];
      } else if (!req.user) {
        whereClause.tipe_aduan = "public";
      }

      const complaint = await Complaint.findOne({
        where: whereClause,
        include: [
          {
            model: Category,
            as: "category",
            attributes: ["id", "name"],
          },
          {
            model: User,
            as: "user",
            attributes: ["id", "username"],
            required: false,
          },
        ],
      });

      if (!complaint) {
        return res.status(404).json({
          status: "error",
          message: "Complaint not found or access denied",
          code: "NOT_FOUND",
        });
      }
      const obj = complaint.toJSON();
      if (obj.date_posted) {
        obj.date_posted = moment(obj.date_posted).tz("Asia/Jakarta").format("YYYY-MM-DD HH:mm:ss");
      }
      res.json({
        status: "success",
        data: obj,
      });
    } catch (error) {
      console.error("Get complaint error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Update complaint status (Admin only)
  static async updateComplaintStatus(req, res) {
    try {
      const { status } = req.body;
      const complaintId = req.params.id;

      if (!["Baru", "Diproses", "Selesai", "Ditolak"].includes(status)) {
        return res.status(400).json({
          status: "error",
          message: "Invalid status",
          code: "INVALID_STATUS",
        });
      }

      const [updatedRows] = await Complaint.update({ status }, { where: { id: complaintId } });

      if (updatedRows === 0) {
        return res.status(404).json({
          status: "error",
          message: "Complaint not found",
          code: "NOT_FOUND",
        });
      }

      res.json({
        status: "success",
        message: "Complaint status updated successfully",
        data: {
          complaint_id: parseInt(complaintId),
          new_status: status,
          updated_at: moment().tz("Asia/Jakarta").format("YYYY-MM-DD HH:mm:ss"),
        },
      });
    } catch (error) {
      console.error("Update complaint status error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Delete complaint (Admin only)
  static async deleteComplaint(req, res) {
    try {
      const complaintId = req.params.id;

      // Check if complaint exists
      const complaint = await Complaint.findByPk(complaintId);

      if (!complaint) {
        return res.status(404).json({
          status: "error",
          message: "Complaint not found",
          code: "NOT_FOUND",
        });
      }

      // Delete the complaint
      const deletedRows = await Complaint.destroy({
        where: { id: complaintId },
      });

      if (deletedRows === 0) {
        return res.status(404).json({
          status: "error",
          message: "Complaint not found",
          code: "NOT_FOUND",
        });
      }

      res.json({
        status: "success",
        message: "Complaint deleted successfully",
        data: {
          complaint_id: parseInt(complaintId),
          deleted_at: moment().tz("Asia/Jakarta").format("YYYY-MM-DD HH:mm:ss"),
        },
      });
    } catch (error) {
      console.error("Delete complaint error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }
}

module.exports = ComplaintController;
