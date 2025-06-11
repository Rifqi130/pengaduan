const { DataTypes, Model } = require("sequelize");
const { sequelize } = require("../config/database");

class Complaint extends Model {
  // Instance method to get complaint with category name
  getComplaintWithCategory() {
    const complaint = this.toJSON();
    if (this.category) {
      complaint.category_name = this.category.name;
    }

    // Add file URL if lampiran exists
    if (this.lampiran) {
      complaint.lampiran_url = `/api/files/attachment/${this.lampiran}`;
    }

    return complaint;
  }
}

Complaint.init(
  {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true,
      autoIncrement: true,
    },
    user_id: {
      type: DataTypes.INTEGER,
      allowNull: true, // NULL for anonymous complaints
      references: {
        model: "users",
        key: "id",
      },
      onDelete: "SET NULL",
    },
    category_id: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: "categories",
        key: "id",
      },
      onDelete: "RESTRICT",
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: false,
      validate: {
        notEmpty: true,
        len: [1, 255],
      },
    },
    description: {
      type: DataTypes.TEXT,
      allowNull: false,
      validate: {
        notEmpty: true,
      },
    },
    // Reporter data (optional)
    nama_pelapor: {
      type: DataTypes.STRING(100),
      allowNull: true,
    },
    jenis_kelamin: {
      type: DataTypes.ENUM("Laki-laki", "Perempuan"),
      allowNull: true,
    },
    nim: {
      type: DataTypes.STRING(20),
      allowNull: true,
    },
    whatsapp: {
      type: DataTypes.STRING(20),
      allowNull: true,
    },
    email_pelapor: {
      type: DataTypes.STRING(100),
      allowNull: true,
      validate: {
        isEmail: true,
      },
    },
    // Incident data
    tanggal_kejadian: {
      type: DataTypes.DATEONLY,
      allowNull: false,
      validate: {
        isDate: true,
        notEmpty: true,
      },
    },
    lokasi_kejadian: {
      type: DataTypes.STRING(255),
      allowNull: false,
      validate: {
        notEmpty: true,
      },
    },
    // Status and type
    status: {
      type: DataTypes.ENUM("Baru", "Diproses", "Selesai", "Ditolak"),
      allowNull: false,
      defaultValue: "Baru",
    },
    tipe_aduan: {
      type: DataTypes.ENUM("private", "public"),
      allowNull: false,
      defaultValue: "private",
    },
    // Attachment
    lampiran: {
      type: DataTypes.STRING(255),
      allowNull: true,
    },
    lampiran_path: {
      type: DataTypes.STRING(500),
      allowNull: true,
    },
  },
  {
    sequelize,
    modelName: "Complaint",
    tableName: "complaints",
    timestamps: true,
    createdAt: "date_posted",
    updatedAt: "last_updated",
    indexes: [
      {
        fields: ["user_id"],
      },
      {
        fields: ["category_id"],
      },
      {
        fields: ["status"],
      },
      {
        fields: ["tipe_aduan"],
      },
      {
        fields: ["date_posted"],
      },
    ],
  }
);

module.exports = Complaint;
