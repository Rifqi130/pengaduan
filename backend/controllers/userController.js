const { User, Complaint, Category } = require('../models');

class UserController {
  // Get current user profile
  static async getProfile(req, res) {
    try {
      const user = await User.findByPk(req.user.id, {
        attributes: { exclude: ['password'] }
      });
      
      if (!user) {
        return res.status(404).json({
          status: 'error',
          message: 'User not found',
          code: 'USER_NOT_FOUND'
        });
      }
      
      res.json({
        status: 'success',
        data: user
      });
      
    } catch (error) {
      console.error('Get user profile error:', error);
      res.status(500).json({
        status: 'error',
        message: 'Internal server error',
        code: 'SERVER_ERROR'
      });
    }
  }

  // Get current user's complaints
  static async getUserComplaints(req, res) {
    try {
      const { status, category } = req.query;
      
      const whereClause = { user_id: req.user.id };
      
      if (status) {
        whereClause.status = status;
      }
      
      const includeClause = [
        {
          model: Category,
          as: 'category',
          attributes: ['id', 'name'],
          where: category ? { name: category } : undefined
        }
      ];
      
      const complaints = await Complaint.findAll({
        where: whereClause,
        include: includeClause,
        attributes: ['id', 'title', 'status', 'tipe_aduan', 'date_posted'],
        order: [['date_posted', 'DESC']]
      });
      
      res.json({
        status: 'success',
        data: {
          complaints: complaints,
          total_complaints: complaints.length
        }
      });
      
    } catch (error) {
      console.error('Get user complaints error:', error);
      res.status(500).json({
        status: 'error',
        message: 'Internal server error',
        code: 'SERVER_ERROR'
      });
    }
  }
}

module.exports = UserController;
