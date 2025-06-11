const { DataTypes, Model } = require("sequelize");
const { sequelizePg } = require("../../config/database");

class ComplaintLog extends Model {}

ComplaintLog.init(
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    complaint_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      comment: "Reference to MySQL complaint ID",
    },
    user_id: {
      type: DataTypes.INTEGER,
      allowNull: true,
      comment: "Reference to MySQL user ID",
    },
    action: {
      type: DataTypes.ENUM("created", "updated", "status_changed", "deleted"),
      allowNull: false,
    },
    old_status: {
      type: DataTypes.STRING(50),
      allowNull: true,
    },
    new_status: {
      type: DataTypes.STRING(50),
      allowNull: true,
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
    ip_address: {
      type: DataTypes.INET,
      allowNull: true,
    },
    user_agent: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
    created_at: {
      type: DataTypes.DATE,
      defaultValue: DataTypes.NOW,
    },
  },
  {
    sequelize: sequelizePg,
    modelName: "ComplaintLog",
    tableName: "complaint_logs",
    timestamps: false,
    indexes: [
      {
        fields: ["complaint_id"],
      },
      {
        fields: ["user_id"],
      },
      {
        fields: ["action"],
      },
      {
        fields: ["created_at"],
      },
    ],
  }
);

module.exports = ComplaintLog;
