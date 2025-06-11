const { DataTypes } = require("sequelize");
const { sequelizePg } = require("../../config/database");

const Complaint = sequelizePg.define("complaints", {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true,
  },
  title: {
    type: DataTypes.STRING(200),
    allowNull: false,
  },
  description: {
    type: DataTypes.TEXT,
    allowNull: false,
  },
  status: {
    type: DataTypes.ENUM("pending", "in_progress", "resolved", "rejected"),
    defaultValue: "pending",
    allowNull: false,
  },
  priority: {
    type: DataTypes.ENUM("low", "medium", "high", "urgent"),
    defaultValue: "medium",
    allowNull: false,
  },
  user_id: {
    type: DataTypes.INTEGER,
    allowNull: false,
    references: {
      model: "users",
      key: "id",
    },
  },
  category_id: {
    type: DataTypes.INTEGER,
    allowNull: false,
    references: {
      model: "categories",
      key: "id",
    },
  },
  admin_response: {
    type: DataTypes.TEXT,
    allowNull: true,
  },
  attachment_path: {
    type: DataTypes.STRING(500),
    allowNull: true,
  },
  resolved_at: {
    type: DataTypes.DATE,
    allowNull: true,
  },
}, {
  tableName: "complaints",
  timestamps: true,
});

module.exports = Complaint;
