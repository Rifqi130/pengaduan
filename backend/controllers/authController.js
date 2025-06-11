const jwt = require("jsonwebtoken");
const { User } = require("../models");
const { validationResult } = require("express-validator");
const { Op } = require("sequelize");

class AuthController {
  // Register new user
  static async register(req, res) {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(422).json({
          status: "error",
          message: "Validation failed",
          errors: errors.mapped(),
        });
      }

      const { username, email, password } = req.body;

      // Cegah register dengan username/email admin
      if (username.trim().toLowerCase() === "admin" || email.trim().toLowerCase() === "admin@example.com") {
        return res.status(403).json({
          status: "error",
          message: "Registrasi dengan username/email admin tidak diperbolehkan",
          code: "FORBIDDEN_ADMIN_REGISTER",
        });
      }

      // Check if user already exists
      const existingUser = await User.findOne({
        where: {
          [Op.or]: [{ username }, { email }],
        },
      });
      if (existingUser) {
        return res.status(400).json({
          status: "error",
          message: "Username or email already exists",
          code: "USER_EXISTS",
        });
      }

      // Create new user
      const user = await User.create({
        username,
        email,
        password,
        role: "mahasiswa",
      });

      res.status(201).json({
        status: "success",
        message: "User registered successfully",
        data: {
          user_id: user.id,
          username: user.username,
          email: user.email,
          role: user.role,
        },
      });
    } catch (error) {
      console.error("Registration error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Login user
  static async login(req, res) {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(422).json({
          status: "error",
          message: "Validation failed",
          errors: errors.mapped(),
        });
      }

      const { username, password, role } = req.body;

      // Find user
      const user = await User.findOne({
        where: {
          username,
          role,
          is_active: true,
        },
      });

      if (!user) {
        return res.status(401).json({
          status: "error",
          message: "Invalid credentials",
          code: "INVALID_CREDENTIALS",
        });
      }

      // Verify password
      const isPasswordValid = await user.validatePassword(password);
      if (!isPasswordValid) {
        return res.status(401).json({
          status: "error",
          message: "Invalid credentials",
          code: "INVALID_CREDENTIALS",
        });
      }

      // Generate JWT token
      const token = jwt.sign({ userId: user.id, username: user.username, role: user.role }, process.env.JWT_SECRET, { expiresIn: process.env.JWT_EXPIRES_IN });

      res.json({
        status: "success",
        message: "Login successful",
        data: {
          token: token,
          user: user.toSafeObject(),
        },
      });
    } catch (error) {
      console.error("Login error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }

  // Logout user
  static async logout(req, res) {
    res.json({
      status: "success",
      message: "Logout successful",
    });
  }

  // Register admin untuk demo
  static async registerAdmin(req, res) {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(422).json({
          status: "error",
          message: "Validation failed",
          errors: errors.mapped(),
        });
      }

      const { username, email, password } = req.body;

      // Check if user already exists
      const existingUser = await User.findOne({
        where: {
          [Op.or]: [{ username }, { email }],
        },
      });
      if (existingUser) {
        return res.status(400).json({
          status: "error",
          message: "Username or email already exists",
          code: "USER_EXISTS",
        });
      }

      // Create new admin user
      const user = await User.create({
        username,
        email,
        password,
        role: "admin",
      });

      res.status(201).json({
        status: "success",
        message: "Admin registered successfully",
        data: {
          user_id: user.id,
          username: user.username,
          email: user.email,
          role: user.role,
        },
      });
    } catch (error) {
      console.error("Admin registration error:", error);
      res.status(500).json({
        status: "error",
        message: "Internal server error",
        code: "SERVER_ERROR",
      });
    }
  }
}

module.exports = AuthController;
