const { DataTypes, Model } = require('sequelize');
const { sequelize } = require('../config/database');
const bcrypt = require('bcryptjs');

class User extends Model {
  // Instance method to check password
  async validatePassword(password) {
    return await bcrypt.compare(password, this.password);
  }

  // Instance method to get safe user data
  toSafeObject() {
    const { password, ...safeUser } = this.toJSON();
    return safeUser;
  }

  // Static method to hash password
  static async hashPassword(password) {
    return await bcrypt.hash(password, 12);
  }
}

User.init({
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  username: {
    type: DataTypes.STRING(50),
    allowNull: false,
    unique: true,
    validate: {
      len: [3, 50],
      notEmpty: true
    }
  },
  email: {
    type: DataTypes.STRING(100),
    allowNull: false,
    unique: true,
    validate: {
      isEmail: true,
      notEmpty: true
    }
  },
  password: {
    type: DataTypes.STRING(255),
    allowNull: false,
    validate: {
      len: [6, 255],
      notEmpty: true
    }
  },
  role: {
    type: DataTypes.ENUM('admin', 'mahasiswa'),
    allowNull: false,
    defaultValue: 'mahasiswa'
  },
  full_name: {
    type: DataTypes.STRING(100),
    allowNull: true
  },
  nim: {
    type: DataTypes.STRING(20),
    allowNull: true
  },
  phone: {
    type: DataTypes.STRING(20),
    allowNull: true
  },
  is_active: {
    type: DataTypes.BOOLEAN,
    allowNull: false,
    defaultValue: true
  }
}, {
  sequelize,
  modelName: 'User',
  tableName: 'users',
  timestamps: true,
  createdAt: 'created_at',
  updatedAt: 'updated_at',
  hooks: {
    beforeCreate: async (user) => {
      if (user.password) {
        user.password = await User.hashPassword(user.password);
      }
    },
    beforeUpdate: async (user) => {
      if (user.changed('password')) {
        user.password = await User.hashPassword(user.password);
      }
    }
  }
});

module.exports = User;
