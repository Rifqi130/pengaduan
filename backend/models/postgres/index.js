const { sequelizePg } = require("../../config/database");
const { initComplaintLog } = require("./ComplaintLog");

let models = {};

const initializePostgresModels = () => {
  try {
    if (!sequelizePg) {
      console.warn("⚠️  PostgreSQL sequelize instance not available");
      return {};
    }

    // Initialize models
    models.ComplaintLog = initComplaintLog(sequelizePg);

    console.log("✅ PostgreSQL models initialized successfully");
    return models;
  } catch (error) {
    console.error("❌ Error initializing PostgreSQL models:", error.message);
    return {};
  }
};

const syncPgModels = async () => {
  try {
    if (!sequelizePg) {
      console.warn("⚠️  PostgreSQL not available for sync");
      return false;
    }

    // Initialize models first
    initializePostgresModels();

    // Sync database
    await sequelizePg.sync({ alter: true });
    console.log("✅ PostgreSQL models synced successfully");
    return true;
  } catch (error) {
    console.error("❌ PostgreSQL sync error:", error.message);
    return false;
  }
};

const testPgConnection = async () => {
  try {
    if (!sequelizePg) {
      throw new Error("PostgreSQL sequelize instance not available");
    }

    await sequelizePg.authenticate();
    return true;
  } catch (error) {
    console.warn("⚠️  PostgreSQL connection test failed:", error.message);
    return false;
  }
};

module.exports = {
  sequelizePg,
  models,
  initializePostgresModels,
  syncPgModels,
  testPgConnection,
};
