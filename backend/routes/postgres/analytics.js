const express = require("express");
const { SystemAnalytics, UserActivity, ComplaintLog, sequelizePg } = require("../../models/postgres");
const { Op } = require("sequelize");
const router = express.Router();

// Get system analytics
router.get("/system", async (req, res) => {
  try {
    const analytics = await SystemAnalytics.findAll({
      order: [["metric_name", "ASC"]],
    });

    res.json({
      status: "success",
      data: { analytics },
    });
  } catch (error) {
    console.error("Error fetching system analytics:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to fetch system analytics",
    });
  }
});

// Get daily activity stats
router.get("/daily-activity", async (req, res) => {
  try {
    const { days = 7 } = req.query;
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - parseInt(days));

    const activities = await UserActivity.findAll({
      where: {
        created_at: {
          [Op.gte]: startDate,
        },
      },
      attributes: [[sequelizePg.fn("DATE", sequelizePg.col("created_at")), "date"], [sequelizePg.fn("COUNT", sequelizePg.col("id")), "count"], "activity_type"],
      group: [sequelizePg.fn("DATE", sequelizePg.col("created_at")), "activity_type"],
      order: [[sequelizePg.fn("DATE", sequelizePg.col("created_at")), "DESC"]],
    });

    res.json({
      status: "success",
      data: { activities },
    });
  } catch (error) {
    console.error("Error fetching daily activity:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to fetch daily activity",
    });
  }
});

// Get complaint statistics
router.get("/complaint-stats", async (req, res) => {
  try {
    const { days = 30 } = req.query;
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - parseInt(days));

    const stats = await ComplaintLog.findAll({
      where: {
        created_at: {
          [Op.gte]: startDate,
        },
      },
      attributes: ["action", [sequelizePg.fn("COUNT", sequelizePg.col("id")), "count"]],
      group: ["action"],
      order: [[sequelizePg.fn("COUNT", sequelizePg.col("id")), "DESC"]],
    });

    res.json({
      status: "success",
      data: { stats },
    });
  } catch (error) {
    console.error("Error fetching complaint stats:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to fetch complaint stats",
    });
  }
});

// Update system metric
router.put("/system/:metric_name", async (req, res) => {
  try {
    const { metric_name } = req.params;
    const { metric_value } = req.body;

    const [analytics, created] = await SystemAnalytics.findOrCreate({
      where: { metric_name },
      defaults: {
        metric_value,
        metric_type: "gauge",
        description: `Auto-created metric: ${metric_name}`,
      },
    });

    if (!created) {
      analytics.metric_value = metric_value;
      analytics.last_updated = new Date();
      await analytics.save();
    }

    res.json({
      status: "success",
      data: { analytics },
    });
  } catch (error) {
    console.error("Error updating system metric:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to update system metric",
    });
  }
});

module.exports = router;
