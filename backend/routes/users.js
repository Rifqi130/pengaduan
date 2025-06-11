const express = require('express');
const UserController = require('../controllers/userController');
const router = express.Router();

// Routes (all require authentication via middleware)
router.get('/me', UserController.getProfile);
router.get('/me/complaints', UserController.getUserComplaints);

module.exports = router;
