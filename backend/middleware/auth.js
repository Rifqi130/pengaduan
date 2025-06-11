const jwt = require('jsonwebtoken');
const { User } = require('../models');

// Verify JWT token
const authenticateToken = async (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

  if (!token) {
    return res.status(401).json({
      status: 'error',
      message: 'Access token required',
      code: 'NO_TOKEN'
    });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    
    // Verify user still exists in database
    const user = await User.findOne({
      where: {
        id: decoded.userId,
        is_active: true
      },
      attributes: { exclude: ['password'] }
    });

    if (!user) {
      return res.status(401).json({
        status: 'error',
        message: 'Invalid or inactive user',
        code: 'INVALID_USER'
      });
    }

    req.user = user;
    next();
  } catch (error) {
    return res.status(403).json({
      status: 'error',
      message: 'Invalid or expired token',
      code: 'INVALID_TOKEN'
    });
  }
};

// Check if user is admin
const requireAdmin = (req, res, next) => {
  if (req.user.role !== 'admin') {
    return res.status(403).json({
      status: 'error',
      message: 'Admin access required',
      code: 'ADMIN_REQUIRED'
    });
  }
  next();
};

// Check if user is mahasiswa
const requireMahasiswa = (req, res, next) => {
  if (req.user.role !== 'mahasiswa') {
    return res.status(403).json({
      status: 'error',
      message: 'Student access required',
      code: 'STUDENT_REQUIRED'
    });
  }
  next();
};

module.exports = {
  authenticateToken,
  requireAdmin,
  requireMahasiswa
};
