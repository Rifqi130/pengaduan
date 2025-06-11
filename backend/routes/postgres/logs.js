const express = require("express");
const { ComplaintLog, UserActivity } = require("../../models/postgres");
const router = express.Router();

// Get complaint logs
router.get("/complaints", async (req, res) => {
  try {
    const { complaint_id, user_id, action } = req.query;

    const where = {};
    if (complaint_id) where.complaint_id = complaint_id;
    if (user_id) where.user_id = user_id;
    if (action) where.action = action;

    const logs = await ComplaintLog.findAll({
      where,
      order: [["created_at", "DESC"]],
    });

    res.json({
      status: "success",
      data: { logs },
    });
  } catch (error) {
    console.error("Error fetching complaint logs:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to fetch complaint logs",
    });
  }
});

// Get user activities
router.get("/activities", async (req, res) => {
  try {
    const { user_id, activity_type } = req.query;

    const where = {};
    if (user_id) where.user_id = user_id;
    if (activity_type) where.activity_type = activity_type;

    const activities = await UserActivity.findAll({
      where,
      order: [["created_at", "DESC"]],
      limit: 100, // Limit to recent 100 activities
    });

    res.json({
      status: "success",
      data: { activities },
    });
  } catch (error) {
    console.error("Error fetching user activities:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to fetch user activities",
    });
  }
});

// Create complaint log
router.post("/complaints", async (req, res) => {
  try {
    const log = await ComplaintLog.create(req.body);
    res.status(201).json({
      status: "success",
      data: { log },
    });
  } catch (error) {
    console.error("Error creating complaint log:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to create complaint log",
    });
  }
});

// Create user activity
router.post("/activities", async (req, res) => {
  try {
    const activity = await UserActivity.create(req.body);
    res.status(201).json({
      status: "success",
      data: { activity },
    });
  } catch (error) {
    console.error("Error creating user activity:", error);
    res.status(500).json({
      status: "error",
      message: "Failed to create user activity",
    });
  }
});

module.exports = router;
