const { DataTypes, Model } = require("sequelize");
const { sequelizePg } = require("../../config/database");

class SystemAnalytics extends Model {}

SystemAnalytics.init(
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    metric_name: {
      type: DataTypes.STRING(100),
      allowNull: false,
      unique: true,
    },
    metric_value: {
      type: DataTypes.BIGINT,
      allowNull: false,
      defaultValue: 0,
    },
    metric_type: {
      type: DataTypes.ENUM("counter", "gauge", "histogram"),
      allowNull: false,
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: true,
    },
    last_updated: {
      type: DataTypes.DATE,
      defaultValue: DataTypes.NOW,
    },
    created_at: {
      type: DataTypes.DATE,
      defaultValue: DataTypes.NOW,
    },
  },
  {
    sequelize: sequelizePg,
    modelName: "SystemAnalytics",
    tableName: "system_analytics",
    timestamps: false,
    indexes: [
      {
        fields: ["metric_name"],
      },
      {
        fields: ["metric_type"],
      },
      {
        fields: ["last_updated"],
      },
    ],
  }
);

module.exports = SystemAnalytics;
