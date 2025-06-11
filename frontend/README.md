# Student Complaint System - Frontend

Frontend PHP application for the Student Complaint System that communicates with the REST API backend.

## Overview

This frontend application provides a web interface for:

- **Students**: Submit and track complaints
- **Anonymous users**: Submit public complaints
- **Administrators**: Manage complaints and users

The frontend now uses REST API calls instead of direct database connections, making it completely decoupled from the database layer.

## Architecture

```
Frontend (PHP) --> API Client --> REST API (Node.js) --> Database (MySQL)
```

## Prerequisites

- PHP 7.4 or higher
- Apache/Nginx web server with mod_rewrite enabled
- cURL extension enabled in PHP
- Backend API server running (Node.js application)

## Installation

1. **Clone/Copy the frontend files** to your web server directory
2. **Configure the API endpoint** in `includes/config.php`:
   ```php
   define('API_BASE_URL', 'http://localhost:3000/api');
   ```
3. **Set proper permissions** for upload directories:
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/complaints/
   ```
4. **Ensure the backend API is running** on the configured endpoint

## Configuration

### Main Configuration (`includes/config.php`)

```php
// API Configuration
define('API_BASE_URL', 'http://localhost:3000/api');

// Frontend Configuration
define('FRONTEND_BASE_URL', 'http://localhost/pengaduan/frontend');

// Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);
```

### Web Server Configuration

For Apache, the included `.htaccess` file handles:

- URL rewriting (removes .php extensions)
- Security headers
- File access restrictions

## API Integration

### API Client (`includes/api_client.php`)

The `ApiClient` class handles all communication with the backend:

- **Authentication**: Login/logout with JWT token management
- **Complaints**: CRUD operations for complaints
- **Users**: User management (admin only)
- **File uploads**: Multipart form data for file attachments
- **Error handling**: Graceful fallbacks when API is unavailable

### Key Features

1. **JWT Token Management**

   - Automatic token storage in PHP sessions
   - Token included in Authorization headers
   - Automatic logout on token expiration

2. **File Upload Support**

   - Handles multipart form data for file attachments
   - Supports images and documents up to 5MB
   - Secure file type validation

3. **Error Handling**
   - Connection timeouts and retry logic
   - Graceful degradation when API is unavailable
   - User-friendly error messages

## Pages and Functionality

### Public Pages

- **`index.php`**: Homepage with complaint submission form
- **`public_complaints.php`**: View all public complaints
- **`login.php`**: Student login
- **`register.php`**: Student registration
- **`admin-login.php`**: Administrator login
- **`status.php`**: System status and health check

### Student Pages (Authentication Required)

- **`my_complaints.php`**: View personal complaints
- **`view_complaint.php`**: Detailed complaint view

### Admin Pages (Admin Authentication Required)

- **`dashboard.php`**: Admin dashboard with statistics
- **`complaints.php`**: Manage all complaints
- **`users.php`**: User management

## Usage

### For Students

1. **Register**: Create account via `/register.php`
2. **Login**: Sign in via `/login.php`
3. **Submit Complaints**: Use the form on homepage
4. **Track Complaints**: View status in "My Complaints"

### For Anonymous Users

1. **Submit Public Complaints**: Use homepage form without login
2. **Choose complaint type**: Public (visible to all) or Private
3. **Optional personal data**: Can include contact information

### For Administrators

1. **Login**: Use admin credentials via `/admin-login.php`
2. **Dashboard**: View system statistics
3. **Manage Complaints**: Update status, view details
4. **User Management**: View and manage user accounts

## API Endpoints Used

The frontend communicates with these backend endpoints:

### Authentication

- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout

### Complaints

- `GET /api/complaints` - List complaints
- `POST /api/complaints` - Submit complaint
- `GET /api/complaints/:id` - Get complaint details
- `PUT /api/complaints/:id/status` - Update complaint status

### Users

- `GET /api/users/me` - Current user profile
- `GET /api/users/me/complaints` - User's complaints

### Admin

- `GET /api/admin/dashboard` - Dashboard statistics
- `GET /api/admin/users` - List all users
- `DELETE /api/admin/users/:id` - Delete user

### Categories

- `GET /api/categories` - List categories

## File Structure

```
frontend/
├── includes/
│   ├── config.php          # Configuration settings
│   ├── api_client.php      # API communication class
│   ├── api_functions.php   # Helper functions using API
│   ├── header.php          # Common header
│   └── footer.php          # Common footer
├── uploads/
│   └── complaints/         # Uploaded files
├── *.php                   # Page files
└── .htaccess              # Apache configuration
```

## Security Features

1. **Input Sanitization**: All user inputs are sanitized
2. **File Upload Security**: Type and size validation
3. **JWT Authentication**: Secure token-based auth
4. **CSRF Protection**: Form validation
5. **Access Control**: Role-based page access

## Troubleshooting

### Common Issues

1. **API Connection Failed**

   - Check if backend server is running
   - Verify API_BASE_URL in config.php
   - Check network connectivity

2. **File Upload Errors**

   - Verify upload directory permissions
   - Check file size limits
   - Ensure allowed file types

3. **Authentication Issues**
   - Clear browser cookies/session
   - Check JWT token expiration
   - Verify user credentials

### Debug Information

Visit `/status.php` to check:

- API connectivity status
- Current session information
- System configuration
- Backend health status

## Development

### Adding New Features

1. **Add API endpoint** in backend first
2. **Update ApiClient class** with new method
3. **Add helper function** in api_functions.php
4. **Create/update page** to use new functionality

### Testing

- Use the status page to verify API connectivity
- Check browser console for JavaScript errors
- Monitor backend logs for API errors
- Test with different user roles

## Production Deployment

1. **Update configuration** for production URLs
2. **Disable debug mode** in config.php
3. **Set proper file permissions**
4. **Configure HTTPS** for secure communication
5. **Set up proper backup** for uploaded files

## Support

For issues related to:

- **Frontend/PHP code**: Check this README and status page
- **Backend/API**: Refer to backend documentation
- **Database**: Check backend database configuration

## Version History

- **v2.0**: API integration complete - removed direct database dependencies
- **v1.0**: Initial version with direct database connection

## Dual Backend Integration

The frontend now connects to **two backend servers**:

### MySQL Server (Port 3000) - Main Application

- User authentication and management
- Complaint submission and management
- Categories and core business logic

### PostgreSQL Server (Port 3001) - Analytics & Logging

- Complaint activity logs
- User activity tracking
- System analytics and metrics

## Configuration

Update both API endpoints in `includes/config.php`:

```php
// MySQL Server (Main)
define('API_BASE_URL', 'http://localhost:3000/api');

// PostgreSQL Server (Analytics)
define('PG_API_BASE_URL', 'http://localhost:3001/api');
```
