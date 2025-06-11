const { DataTypes, Model } = require("sequelize");
const { sequelizePg } = require("../../config/database");

class UserActivity extends Model {}

UserActivity.init(
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    user_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      comment: "Reference to MySQL user ID",
    },
    activity_type: {
      type: DataTypes.ENUM("login", "logout", "complaint_submit", "complaint_update", "profile_update"),
      allowNull: false,
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
    session_id: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    created_at: {
      type: DataTypes.DATE,
      defaultValue: DataTypes.NOW,
    },
  },
  {
    sequelize: sequelizePg,
    modelName: "UserActivity",
    tableName: "user_activities",
    timestamps: false,
    indexes: [
      {
        fields: ["user_id"],
      },
      {
        fields: ["activity_type"],
      },
      {
        fields: ["created_at"],
      },
      {
        fields: ["session_id"],
      },
    ],
  }
);

module.exports = UserActivity;
