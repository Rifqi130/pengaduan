const { DataTypes, Model } = require("sequelize");
const { sequelizePg } = require("./index");

class PgLog extends Model {}

PgLog.init(
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    message: {
      type: DataTypes.STRING(255),
      allowNull: false,
    },
    created_at: {
      type: DataTypes.DATE,
      defaultValue: DataTypes.NOW,
    },
  },
  {
    sequelize: sequelizePg,
    modelName: "PgLog",
    tableName: "pg_logs",
    timestamps: false,
  }
);

module.exports = PgLog;
