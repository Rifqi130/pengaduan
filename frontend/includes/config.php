<?php
/**
 * Configuration file for the Student Complaint System Frontend
 * Adjust these settings based on your environment
 */

// API Configuration - MySQL Server (Main) - Updated to localhost
define('API_BASE_URL', 'http://localhost:3000/api');

// API Configuration - PostgreSQL Server (Analytics/Logs) - Updated to localhost
define('PG_API_BASE_URL', 'http://localhost:3001/api');

define('FRONTEND_BASE_URL', 'http://localhost:8080');

// Frontend Configuration - Updated to localhost
# define('FRONTEND_BASE_URL', 'http://localhost/pengaduan/frontend');

// Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Session Configuration (only set if session is not active)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400); // 24 hours
    ini_set('session.gc_maxlifetime', 86400);
    session_start();
}

// Error Reporting (set to 0 in production)
if (getenv('ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Debug mode for GCP deployment
define('DEBUG_MODE', true);
?>
