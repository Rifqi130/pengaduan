const express = require('express');
const AdminController = require('../controllers/adminController');
const { authenticateToken, requireAdmin } = require('../middleware/auth');
const router = express.Router();

// All routes require admin authentication
router.use(authenticateToken, requireAdmin);

// Routes
router.get('/users', AdminController.getAllUsers);
router.delete('/users/:id', AdminController.deleteUser);
router.put('/users/:id/status', AdminController.updateUserStatus);
router.get('/dashboard', AdminController.getDashboard);

module.exports = router;
