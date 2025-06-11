const { DataTypes } = require("sequelize");
const { sequelizePg } = require("../../config/database");

const Category = sequelizePg.define("categories", {
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true,
  },
  name: {
    type: DataTypes.STRING(100),
    allowNull: false,
    unique: true,
  },
  description: {
    type: DataTypes.TEXT,
    allowNull: true,
  },
}, {
  tableName: "categories",
  timestamps: true,
});

module.exports = Category;
