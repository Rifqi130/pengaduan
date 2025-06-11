const express = require('express');
const { body } = require('express-validator');
const AuthController = require('../controllers/authController');
const router = express.Router();

// Validation rules
const registerValidation = [
  body('username').isLength({ min: 3 }).withMessage('Username must be at least 3 characters'),
  body('email').isEmail().withMessage('Please provide a valid email'),
  body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters')
];

const loginValidation = [
  body('username').notEmpty().withMessage('Username is required'),
  body('password').notEmpty().withMessage('Password is required'),
  body('role').isIn(['mahasiswa', 'admin']).withMessage('Role must be mahasiswa or admin')
];

// Routes
router.post('/register', registerValidation, AuthController.register);
router.post('/register/admin', registerValidation, AuthController.registerAdmin);
router.post('/login', loginValidation, AuthController.login);
router.post('/logout', AuthController.logout);

module.exports = router;
