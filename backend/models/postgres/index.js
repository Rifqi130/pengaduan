const { sequelizePg } = require("../../config/database");
const ComplaintLog = require("./ComplaintLog");
const UserActivity = require("./UserActivity");
const SystemAnalytics = require("./SystemAnalytics");

// Define associations
// ComplaintLog and UserActivity are related by user_id but not a direct foreign key relationship
// since they reference MySQL user IDs

// Sync PostgreSQL models
const syncPgModels = async () => {
  try {
    await sequelizePg.sync({ alter: true });
    console.log("✅ PostgreSQL models synchronized");

    // Seed initial analytics data
    const analyticsCount = await SystemAnalytics.count();
    if (analyticsCount === 0) {
      await SystemAnalytics.bulkCreate([
        {
          metric_name: "total_complaints",
          metric_value: 0,
          metric_type: "counter",
          description: "Total number of complaints submitted",
        },
        {
          metric_name: "active_users",
          metric_value: 0,
          metric_type: "gauge",
          description: "Number of active users",
        },
        {
          metric_name: "pending_complaints",
          metric_value: 0,
          metric_type: "gauge",
          description: "Number of pending complaints",
        },
        {
          metric_name: "resolved_complaints",
          metric_value: 0,
          metric_type: "counter",
          description: "Number of resolved complaints",
        },
      ]);
      console.log("✅ PostgreSQL initial analytics data seeded");
    }
  } catch (error) {
    console.error("❌ Failed to sync PostgreSQL models:", error.message);
    throw error;
  }
};

// Test PostgreSQL connection
const testPgConnection = async () => {
  try {
    await sequelizePg.authenticate();
    console.log("✅ PostgreSQL database connection verified");
    return true;
  } catch (error) {
    console.error("❌ PostgreSQL connection failed:", error.message);
    return false;
  }
};

module.exports = {
  sequelizePg,
  ComplaintLog,
  UserActivity,
  SystemAnalytics,
  syncPgModels,
  testPgConnection,
};
