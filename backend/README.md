# Student Complaint System - Dual Server REST API

## Overview

This is a dual-server REST API backend for the Student Complaint System built with Express.js, Sequelize ORM, MySQL, PostgreSQL, and JWT authentication.

## Architecture

### Dual Server Setup

- **MySQL Server (Port 3000)** - Main application server
  - User authentication and management
  - Complaint submission and management
  - Categories and core business logic
- **PostgreSQL Server (Port 3001)** - Analytics and logging server
  - Complaint activity logs
  - User activity tracking
  - System analytics and metrics

## Features

- ✅ Dual database architecture (MySQL + PostgreSQL)
- ✅ Auto table creation for both databases
- ✅ User authentication (JWT-based)
- ✅ Anonymous complaint submission
- ✅ File upload support
- ✅ Role-based access control (Admin/Student)
- ✅ Complaint management with activity logging
- ✅ User management (Admin)
- ✅ Dashboard statistics across both servers
- ✅ Sequelize ORM with models and controllers
- ✅ MVC architecture pattern
- ✅ Cross-server data synchronization

## Prerequisites

- Node.js (v14 or higher)
- MySQL database
- PostgreSQL database
- Existing database schema (from `requirement/create_database.sql`)

## Installation

1. **Install dependencies:**

```bash
cd backend
npm install
```

2. **Configure environment:**

```bash
cp .env.example .env
# Edit .env with your database credentials for both MySQL and PostgreSQL
```

Required environment variables:

```env
# MySQL Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=pengaduan_mahasiswa
DB_USER=root
DB_PASS=

# PostgreSQL Configuration
PG_DB_HOST=localhost
PG_DB_PORT=5432
PG_DB_NAME=pengaduan_mahasiswa
PG_DB_USER=postgres
PG_DB_PASS=

# Server Configuration
PORT=3000
PG_PORT=3001
FRONTEND_URL=http://localhost:5173
```

3. **Create uploads directory:**

```bash
mkdir -p uploads/complaints
```

4. **Start both servers:**

```bash
# Development (starts both MySQL and PostgreSQL servers)
npm run dev

# Production
npm start
```

## Architecture

### Dual Server Structure

```
backend/
├── config/         # Database configurations (MySQL + PostgreSQL)
├── models/         # Sequelize models
│   ├── User.js     # MySQL - User accounts
│   ├── Category.js # MySQL - Complaint categories
│   ├── Complaint.js # MySQL - Main complaints
│   └── postgres/   # PostgreSQL models
│       ├── ComplaintLog.js    # Complaint activity logs
│       ├── UserActivity.js    # User activity tracking
│       └── SystemAnalytics.js # System metrics
├── controllers/    # Business logic controllers
├── routes/         # API route definitions
│   └── postgres/   # PostgreSQL-specific routes
├── middleware/     # Auth, upload, validation
├── uploads/        # File storage
├── server.js       # Main MySQL server
├── server-postgres.js # PostgreSQL server
└── package.json    # Dependencies
```

### Database Models

#### MySQL Models (Port 3000)

- **User** - User accounts (admin/mahasiswa)
- **Category** - Complaint categories
- **Complaint** - Main complaint data

#### PostgreSQL Models (Port 3001)

- **ComplaintLog** - Activity logs for complaints
- **UserActivity** - User session and activity tracking
- **SystemAnalytics** - System metrics and analytics

### Controllers

- **AuthController** - Authentication logic (MySQL)
- **ComplaintController** - Complaint management (MySQL + PostgreSQL logging)
- **UserController** - User profile and complaints (MySQL)
- **AdminController** - Admin operations (MySQL)
- **CategoryController** - Category management (MySQL)
- **LogController** - Activity logging (PostgreSQL)
- **AnalyticsController** - System analytics (PostgreSQL)

## API Endpoints

### MySQL Server (Port 3000)

#### Authentication

- `POST /api/auth/register` - Register new student
- `POST /api/auth/login` - Login (admin/student)
- `POST /api/auth/logout` - Logout

#### Complaints

- `GET /api/complaints` - Get complaints (filtered)
- `POST /api/complaints` - Submit complaint
- `GET /api/complaints/:id` - Get specific complaint
- `PUT /api/complaints/:id/status` - Update status (Admin only)

#### Users

- `GET /api/users/me` - Get current user profile
- `GET /api/users/me/complaints` - Get user's complaints

#### Admin

- `GET /api/admin/users` - Get all users
- `DELETE /api/admin/users/:id` - Delete user
- `GET /api/admin/dashboard` - Dashboard statistics

#### Categories

- `GET /api/categories` - Get all categories

#### Health Check

- `GET /api/health` - MySQL server health check

### PostgreSQL Server (Port 3001)

#### Logs

- `GET /api/logs/complaints` - Get complaint activity logs
- `POST /api/logs/complaints` - Create complaint log
- `GET /api/logs/activities` - Get user activities
- `POST /api/logs/activities` - Create user activity

#### Analytics

- `GET /api/analytics/system` - Get system metrics
- `GET /api/analytics/daily-activity` - Get daily activity stats
- `PUT /api/analytics/system/:metric_name` - Update system metric

#### Health Check

- `GET /api/health` - PostgreSQL server health check

## Frontend Integration

To connect to both servers from your frontend:

```javascript
// MySQL server (main app)
const mysqlAPI = axios.create({
  baseURL: "http://localhost:3000/api",
});

// PostgreSQL server (analytics)
const postgresAPI = axios.create({
  baseURL: "http://localhost:3001/api",
});

// Example: Submit complaint (MySQL) and log activity (PostgreSQL)
const submitComplaint = async (complaintData) => {
  const complaint = await mysqlAPI.post("/complaints", complaintData);
  await postgresAPI.post("/logs/complaints", {
    complaint_id: complaint.data.id,
    action: "created",
    description: "Complaint submitted",
  });
  return complaint;
};
```

## Database Auto-Creation

Both servers automatically create tables on startup:

- **MySQL**: Users, Categories, Complaints tables
- **PostgreSQL**: ComplaintLogs, UserActivities, SystemAnalytics tables

## Response Format

All endpoints return data without pagination:

```json
{
  "status": "success",
  "data": {
    "complaints": [...],
    "total": 25
  }
}
```

## Security Features

- Helmet.js for security headers
- CORS configuration for both servers
- JWT authentication
- Input validation
- File type validation
- SQL injection prevention (Sequelize ORM)
- Password hashing with bcrypt
- Cross-server request validation

## Development

**Run both servers:**

```bash
npm run dev
```

This will start:

- MySQL server on port 3000
- PostgreSQL server on port 3001

**Model synchronization:**

- Both databases auto-sync in development mode
- Production uses existing schema

## Production Deployment

1. Set `NODE_ENV=production`
2. Configure both database connections
3. Set proper CORS origins for both servers
4. Enable HTTPS for both endpoints
5. Set up database backups for both MySQL and PostgreSQL
6. Configure logging and monitoring
7. Disable auto-sync for production

## API Testing

Test both servers:

```bash
# MySQL server health
curl http://localhost:3000/api/health

# PostgreSQL server health
curl http://localhost:3001/api/health

# Submit complaint (MySQL)
curl -X POST http://localhost:3000/api/complaints \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","description":"Test complaint"}'

# Get analytics (PostgreSQL)
curl http://localhost:3001/api/analytics/system
```

## Changes from Single Server

- ✅ **Dual server architecture** - Separated concerns between MySQL and PostgreSQL
- ✅ **Auto table creation** - Both databases create tables automatically
- ✅ **Cross-server logging** - Activities logged to PostgreSQL when actions occur on MySQL
- ✅ **Scalable analytics** - Dedicated PostgreSQL server for analytics and reporting
- ✅ **Frontend flexibility** - Frontend can connect to both servers independently

## FAQ

### Why use two different databases?

- **MySQL**: Optimized for transactional data (users, complaints, categories)
- **PostgreSQL**: Optimized for analytics, logging, and complex queries
- **Scalability**: Each server can be scaled independently based on load
- **Separation of concerns**: Core business logic separate from analytics

### How do the servers communicate?

- Servers don't communicate directly
- Frontend acts as the coordinator between both servers
- Shared data references (like user_id, complaint_id) maintain relationships

### Can I run only one server?

Yes, you can modify the startup script to run only MySQL or only PostgreSQL server as needed.
