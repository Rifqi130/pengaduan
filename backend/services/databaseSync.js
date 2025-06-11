const { User: MySQLUser, Category: MySQLCategory, Complaint: MySQLComplaint } = require("../models");
const { User: PgUser, Category: PgCategory, Complaint: PgComplaint } = require("../models/postgres");
const { testPgConnection } = require("../config/database");

class DatabaseSyncService {
  constructor() {
    this.isPostgresAvailable = false;
    this.syncQueue = [];
    this.isProcessing = false;
  }

  async initialize() {
    this.isPostgresAvailable = await testPgConnection();
    if (this.isPostgresAvailable) {
      console.log("✅ Database sync service initialized");
      await this.performInitialSync();
      this.startQueueProcessor();
    } else {
      console.warn("⚠️  PostgreSQL not available, sync service disabled");
    }
  }

  async performInitialSync() {
    try {
      console.log("🔄 Performing initial data synchronization...");

      // Sync Categories
      await this.syncAllCategories();

      // Sync Users
      await this.syncAllUsers();

      // Sync Complaints
      await this.syncAllComplaints();

      console.log("✅ Initial synchronization completed");
    } catch (error) {
      console.error("❌ Initial sync failed:", error);
    }
  }

  async syncAllCategories() {
    try {
      const mysqlCategories = await MySQLCategory.findAll();

      for (const category of mysqlCategories) {
        await PgCategory.upsert({
          id: category.id,
          name: category.name,
          description: category.description,
          createdAt: category.createdAt,
          updatedAt: category.updatedAt,
        });
      }

      console.log(`✅ Synced ${mysqlCategories.length} categories`);
    } catch (error) {
      console.error("❌ Category sync failed:", error);
    }
  }

  async syncAllUsers() {
    try {
      const mysqlUsers = await MySQLUser.findAll();

      for (const user of mysqlUsers) {
        await PgUser.upsert({
          id: user.id,
          username: user.username,
          email: user.email,
          password: user.password,
          full_name: user.full_name,
          role: user.role,
          is_active: user.is_active,
          createdAt: user.createdAt,
          updatedAt: user.updatedAt,
        });
      }

      console.log(`✅ Synced ${mysqlUsers.length} users`);
    } catch (error) {
      console.error("❌ User sync failed:", error);
    }
  }

  async syncAllComplaints() {
    try {
      const mysqlComplaints = await MySQLComplaint.findAll();

      for (const complaint of mysqlComplaints) {
        await PgComplaint.upsert({
          id: complaint.id,
          title: complaint.title,
          description: complaint.description,
          status: complaint.status,
          priority: complaint.priority,
          user_id: complaint.user_id,
          category_id: complaint.category_id,
          admin_response: complaint.admin_response,
          attachment_path: complaint.attachment_path,
          resolved_at: complaint.resolved_at,
          createdAt: complaint.createdAt,
          updatedAt: complaint.updatedAt,
        });
      }

      console.log(`✅ Synced ${mysqlComplaints.length} complaints`);
    } catch (error) {
      console.error("❌ Complaint sync failed:", error);
    }
  }

  addToSyncQueue(operation, model, data) {
    if (!this.isPostgresAvailable) return;

    this.syncQueue.push({
      operation,
      model,
      data,
      timestamp: new Date(),
    });
  }

  startQueueProcessor() {
    setInterval(async () => {
      if (this.syncQueue.length > 0 && !this.isProcessing) {
        await this.processQueue();
      }
    }, 1000); // Process every second
  }

  async processQueue() {
    if (this.isProcessing || this.syncQueue.length === 0) return;

    this.isProcessing = true;

    try {
      const batch = this.syncQueue.splice(0, 10); // Process 10 items at a time

      for (const item of batch) {
        await this.processSyncItem(item);
      }
    } catch (error) {
      console.error("❌ Queue processing failed:", error);
    } finally {
      this.isProcessing = false;
    }
  }

  async processSyncItem(item) {
    try {
      const { operation, model, data } = item;
      let PgModel;

      switch (model) {
        case "User":
          PgModel = PgUser;
          break;
        case "Category":
          PgModel = PgCategory;
          break;
        case "Complaint":
          PgModel = PgComplaint;
          break;
        default:
          console.warn(`Unknown model: ${model}`);
          return;
      }

      switch (operation) {
        case "create":
        case "update":
          await PgModel.upsert(data);
          break;
        case "delete":
          await PgModel.destroy({ where: { id: data.id } });
          break;
      }
    } catch (error) {
      console.error(`❌ Sync item processing failed:`, error);
    }
  }

  // Public methods to be called from controllers
  syncCreate(model, data) {
    this.addToSyncQueue("create", model, data);
  }

  syncUpdate(model, data) {
    this.addToSyncQueue("update", model, data);
  }

  syncDelete(model, data) {
    this.addToSyncQueue("delete", model, data);
  }
}

const databaseSyncService = new DatabaseSyncService();

const initializeDatabaseSync = async () => {
  await databaseSyncService.initialize();
};

module.exports = {
  databaseSyncService,
  initializeDatabaseSync,
};
