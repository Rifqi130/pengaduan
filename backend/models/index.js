const { sequelize, testConnection } = require("../config/database");
const bcrypt = require("bcryptjs");

// Import models with error handling
let User, Category, Complaint;

try {
  User = require("./User");
  Category = require("./Category");
  Complaint = require("./Complaint");
} catch (error) {
  console.error("Error importing models:", error.message);
  throw error;
}

// Definisikan relasi antar model
try {
  User.hasMany(Complaint, { foreignKey: "user_id", as: "complaints" });
  Complaint.belongsTo(User, { foreignKey: "user_id", as: "user" });

  Category.hasMany(Complaint, { foreignKey: "category_id", as: "complaints" });
  Complaint.belongsTo(Category, { foreignKey: "category_id", as: "category" });
} catch (error) {
  console.error("Error defining model associations:", error.message);
}

// Add sync hooks untuk automatic synchronization
const addSyncHooks = () => {
  // Import sync service (delayed to avoid circular dependency)
  let syncService;
  try {
    syncService = require("../services/databaseSync").databaseSyncService;
  } catch (error) {
    console.warn("Sync service not available:", error.message);
    return;
  }

  if (syncService) {
    // User hooks
    User.addHook("afterCreate", (user, options) => {
      syncService.syncCreate("User", user.toJSON());
    });

    User.addHook("afterUpdate", (user, options) => {
      syncService.syncUpdate("User", user.toJSON());
    });

    User.addHook("afterDestroy", (user, options) => {
      syncService.syncDelete("User", { id: user.id });
    });

    // Category hooks
    Category.addHook("afterCreate", (category, options) => {
      syncService.syncCreate("Category", category.toJSON());
    });

    Category.addHook("afterUpdate", (category, options) => {
      syncService.syncUpdate("Category", category.toJSON());
    });

    Category.addHook("afterDestroy", (category, options) => {
      syncService.syncDelete("Category", { id: category.id });
    });

    // Complaint hooks
    Complaint.addHook("afterCreate", (complaint, options) => {
      syncService.syncCreate("Complaint", complaint.toJSON());
    });

    Complaint.addHook("afterUpdate", (complaint, options) => {
      syncService.syncUpdate("Complaint", complaint.toJSON());
    });

    Complaint.addHook("afterDestroy", (complaint, options) => {
      syncService.syncDelete("Complaint", { id: complaint.id });
    });
  }
};

// Fungsi untuk sinkronisasi model ke database
const syncModels = async () => {
  try {
    // Test connection first
    const isConnected = await testConnection();
    if (!isConnected) {
      throw new Error("Database connection not available");
    }

    await sequelize.sync({ alter: true });
    console.log("✅ MySQL models synchronized to database");

    // Add sync hooks after models are synced
    addSyncHooks();

    // --- Seeding kategori ---
    try {
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
        console.log("✅ Default categories inserted");
      }
    } catch (error) {
      console.error("Warning: Could not seed categories:", error.message);
    }
    // --- Akhir seeding kategori ---

    // --- Seeding admin default jika belum ada ---
    try {
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
        console.log("✅ Default admin created (username: admin, password: admin123)");
      }
    } catch (error) {
      console.error("Warning: Could not seed admin user:", error.message);
    }
    // --- Akhir seeding admin ---
  } catch (error) {
    console.error("❌ Failed to sync MySQL models:", error.message);
    throw error;
  }
};

// Export with error handling
module.exports = {
  sequelize,
  User: User || null,
  Category: Category || null,
  Complaint: Complaint || null,
  syncModels,
  testConnection,
  testPgConnection: () => {
    try {
      const { testPgConnection } = require("../config/database");
      return testPgConnection();
    } catch (error) {
      console.warn("PostgreSQL connection not available");
      return Promise.resolve(false);
    }
  },
};
