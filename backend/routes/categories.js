const express = require('express');
const CategoryController = require('../controllers/categoryController');
const router = express.Router();

// Routes
router.get('/', CategoryController.getCategories);

module.exports = router;
