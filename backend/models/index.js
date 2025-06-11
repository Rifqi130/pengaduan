const { sequelize, testConnection } = require("../config/database");
const { testPgConnection } = require("./postgres");
const User = require("./User");
const Category = require("./Category");
const Complaint = require("./Complaint");
const bcrypt = require("bcryptjs"); // Sudah ada

// Definisikan relasi antar model
User.hasMany(Complaint, { foreignKey: "user_id", as: "complaints" });
Complaint.belongsTo(User, { foreignKey: "user_id", as: "user" });

Category.hasMany(Complaint, { foreignKey: "category_id", as: "complaints" });
Complaint.belongsTo(Category, { foreignKey: "category_id", as: "category" });

// Fungsi untuk sinkronisasi model ke database
const syncModels = async () => {
  try {
    await sequelize.sync({ alter: true });
    console.log("✅ Semua model disinkronkan ke database");

    // --- Seeding kategori ---
    const kategoriAwal = [
      { name: "Fasilitas", description: "Pengaduan terkait fasilitas kampus" },
      { name: "Akademik", description: "Pengaduan terkait kegiatan akademik" },
      { name: "Layanan", description: "Pengaduan terkait layanan kampus" },
      { name: "Keuangan", description: "Pengaduan terkait keuangan dan pembayaran" },
      { name: "Lainnya", description: "Pengaduan kategori lainnya" },
    ];
    const kategoriCount = await Category.count();
    if (kategoriCount === 0) {
      await Category.bulkCreate(kategoriAwal);
      console.log("✅ Data kategori awal berhasil diinsert");
    }
    // --- Akhir seeding kategori ---

    // --- Seeding admin default jika belum ada ---
    const adminUsername = "admin";
    const adminEmail = "admin@example.com";
    const adminFullName = "Administrator";
    const adminPassword = "admin123";
    const adminExists = await User.findOne({ where: { username: adminUsername, role: "admin" } });
    if (!adminExists) {
      const hashed = await bcrypt.hash(adminPassword, 12);
      await User.create({
        username: adminUsername,
        email: adminEmail,
        password: hashed,
        role: "admin",
        full_name: adminFullName,
        is_active: true,
      });
      console.log("✅ Admin default berhasil diinsert (username: admin, password: admin123)");
    }
    // --- Akhir seeding admin ---
  } catch (error) {
    console.error("❌ Gagal sinkronisasi model:", error.message);
  }
};

module.exports = {
  sequelize,
  User,
  Category,
  Complaint,
  syncModels,
  testConnection,
  testPgConnection,
};
