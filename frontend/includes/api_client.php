<?php
/**
 * API Client for Student Complaint System
 * Handles all communication with the REST API backend
 */

require_once 'config.php';

class ApiClient
{
    private $baseUrl;
    private $pgBaseUrl;
    private $timeout;
    private $userAgent;

    public function __construct($baseUrl = null, $pgBaseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?: API_BASE_URL, '/');
        $this->pgBaseUrl = rtrim($pgBaseUrl ?: PG_API_BASE_URL, '/');
        $this->timeout = 60; // Increased timeout for GCP
        $this->userAgent = 'StudentComplaintSystem/1.0 PHP Client';
    }

    /**
     * Make HTTP request to API endpoint
     */
    private function makeRequest($method, $endpoint, $data = null, $headers = [])
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        // Debug logging for GCP
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("API Request: {$method} {$url}");
        }

        // Initialize cURL
        $ch = curl_init();

        // Set basic cURL options - Updated for GCP
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 30, // Added for GCP
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false, // For development only
            CURLOPT_SSL_VERIFYHOST => false, // For development only
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
                'Accept: application/json'
            ], $headers)
        ]);

        // Set method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                // GET is default
                break;
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Debug logging for GCP
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("API Response Code: {$httpCode}");
            if ($error) {
                error_log("API cURL Error: {$error}");
            }
        }

        // Handle cURL errors
        if ($error) {
            throw new Exception("API request failed: " . $error);
        }

        // Parse response
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API");
        }

        return [
            'status_code' => $httpCode,
            'data' => $decodedResponse
        ];
    }

    /**
     * Make multipart form request for file uploads
     */
    private function makeMultipartRequest($endpoint, $fields, $files = [], $headers = [])
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $ch = curl_init();

        // Prepare multipart data
        $postData = [];

        // Add regular fields
        foreach ($fields as $key => $value) {
            $postData[$key] = $value;
        }

        // Add files
        foreach ($files as $key => $filePath) {
            if (file_exists($filePath)) {
                $postData[$key] = new CURLFile($filePath);
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array_merge([
                'Accept: application/json'
            ], $headers)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("API request failed: " . $error);
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API");
        }

        return [
            'status_code' => $httpCode,
            'data' => $decodedResponse
        ];
    }

    /**
     * Get authorization header with JWT token
     */
    private function getAuthHeader()
    {
        if (isset($_SESSION['jwt_token'])) {
            return ['Authorization: Bearer ' . $_SESSION['jwt_token']];
        }
        return [];
    }

    // =============================================
    // Authentication Endpoints
    // =============================================

    public function register($username, $email, $password)
    {
        try {
            $response = $this->makeRequest('POST', '/auth/register', [
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            return $response;
        } catch (Exception $e) {
            error_log("API Register Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function login($username, $password, $role = 'mahasiswa')
    {
        try {
            $response = $this->makeRequest('POST', '/auth/login', [
                'username' => $username,
                'password' => $password,
                'role' => $role
            ]);

            // Store JWT token in session if login successful
            if ($response['status_code'] === 200 && isset($response['data']['data']['token'])) {
                $_SESSION['jwt_token'] = $response['data']['data']['token'];
                $_SESSION['user_id'] = $response['data']['data']['user']['id'];
                $_SESSION['username'] = $response['data']['data']['user']['username'];
                $_SESSION['user_role'] = $response['data']['data']['user']['role'];
            }

            return $response;
        } catch (Exception $e) {
            error_log("API Login Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function registerAdmin($username, $email, $password)
    {
        try {
            $response = $this->makeRequest('POST', '/auth/register/admin', [
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);

            return $response;
        } catch (Exception $e) {
            error_log("API Register Admin Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function logout()
    {
        try {
            $response = $this->makeRequest('POST', '/auth/logout', null, $this->getAuthHeader());

            // Clear session data
            unset($_SESSION['jwt_token']);
            unset($_SESSION['user_id']);
            unset($_SESSION['username']);
            unset($_SESSION['user_role']);

            return $response;
        } catch (Exception $e) {
            error_log("API Logout Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // =============================================
    // Category Endpoints
    // =============================================

    public function getCategories()
    {
        try {
            $response = $this->makeRequest('GET', '/categories');
            return $response;
        } catch (Exception $e) {
            error_log("API Get Categories Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // =============================================
    // Complaint Endpoints
    // =============================================

    public function submitComplaint($data, $filePath = null)
    {
        try {
            if ($filePath && file_exists($filePath)) {
                // Use multipart form for file upload
                $fields = [];
                foreach ($data as $key => $value) {
                    $fields[$key] = $value;
                }

                $files = ['lampiran' => $filePath];
                $headers = $this->getAuthHeader();

                $response = $this->makeMultipartRequest('/complaints', $fields, $files, $headers);
            } else {
                // Use JSON for non-file submissions
                $headers = $this->getAuthHeader();
                $response = $this->makeRequest('POST', '/complaints', $data, $headers);
            }

            return $response;
        } catch (Exception $e) {
            error_log("API Submit Complaint Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getComplaints($filters = [])
    {
        try {
            $queryString = '';
            if (!empty($filters)) {
                $queryString = '?' . http_build_query($filters);
            }

            $url = '/complaints' . $queryString;
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', $url, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Complaints Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getComplaintById($id)
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', '/complaints/' . $id, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Complaint By ID Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function updateComplaintStatus($id, $status)
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('PUT', '/complaints/' . $id . '/status', [
                'status' => $status
            ], $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Update Complaint Status Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function deleteComplaint($id)
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('DELETE', '/complaints/' . $id, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Delete Complaint Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['message' => 'Failed to delete complaint']
            ];
        }
    }

    // =============================================
    // User Endpoints
    // =============================================

    public function getCurrentUser()
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', '/users/me', null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Current User Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getCurrentUserComplaints($filters = [])
    {
        try {
            $queryString = '';
            if (!empty($filters)) {
                $queryString = '?' . http_build_query($filters);
            }

            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', '/users/me/complaints' . $queryString, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Current User Complaints Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // =============================================
    // Admin Endpoints
    // =============================================

    public function getDashboardStats()
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', '/admin/dashboard', null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Dashboard Stats Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getAllUsers($filters = [])
    {
        try {
            $queryString = '';
            if (!empty($filters)) {
                $queryString = '?' . http_build_query($filters);
            }

            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('GET', '/admin/users' . $queryString, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Get All Users Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function deleteUser($userId)
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('DELETE', '/admin/users/' . $userId, null, $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Delete User Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function updateUserStatus($userId, $isActive)
    {
        try {
            $headers = $this->getAuthHeader();
            $response = $this->makeRequest('PUT', '/admin/users/' . $userId . '/status', [
                'is_active' => $isActive
            ], $headers);
            return $response;
        } catch (Exception $e) {
            error_log("API Update User Status Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // =============================================
    // Health Check
    // =============================================

    public function healthCheck()
    {
        try {
            $response = $this->makeRequest('GET', '/health');
            return $response;
        } catch (Exception $e) {
            error_log("API Health Check Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // =============================================
    // PostgreSQL Analytics Endpoints
    // =============================================

    public function getComplaintLogs($complaintId = null)
    {
        try {
            $queryString = $complaintId ? '?complaint_id=' . $complaintId : '';
            $response = $this->makeRequestToPg('GET', '/logs/complaints' . $queryString);
            return $response;
        } catch (Exception $e) {
            error_log("API Get Complaint Logs Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function logComplaintActivity($complaintId, $action, $description)
    {
        try {
            $response = $this->makeRequestToPg('POST', '/logs/complaints', [
                'complaint_id' => $complaintId,
                'action' => $action,
                'description' => $description
            ]);
            return $response;
        } catch (Exception $e) {
            error_log("API Log Complaint Activity Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getSystemAnalytics()
    {
        try {
            $response = $this->makeRequestToPg('GET', '/analytics/system');
            return $response;
        } catch (Exception $e) {
            error_log("API Get System Analytics Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function getUserActivities($filters = [])
    {
        try {
            $queryString = '';
            if (!empty($filters)) {
                $queryString = '?' . http_build_query($filters);
            }
            $response = $this->makeRequestToPg('GET', '/logs/activities' . $queryString);
            return $response;
        } catch (Exception $e) {
            error_log("API Get User Activities Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    public function logUserActivity($userId, $activityType, $description)
    {
        try {
            $response = $this->makeRequestToPg('POST', '/logs/activities', [
                'user_id' => $userId,
                'activity_type' => $activityType,
                'description' => $description
            ]);
            return $response;
        } catch (Exception $e) {
            error_log("API Log User Activity Error: " . $e->getMessage());
            return [
                'status_code' => 500,
                'data' => ['status' => 'error', 'message' => 'Connection error']
            ];
        }
    }

    // Helper method untuk request ke PostgreSQL server
    private function makeRequestToPg($method, $endpoint, $data = null, $headers = [])
    {
        $url = $this->pgBaseUrl . '/' . ltrim($endpoint, '/');

        // Initialize cURL
        $ch = curl_init();

        // Set basic cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false, // For development only
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
                'Accept: application/json'
            ], $headers)
        ]);

        // Set method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                // GET is default
                break;
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($error) {
            throw new Exception("API request failed: " . $error);
        }

        // Parse response
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from API");
        }

        return [
            'status_code' => $httpCode,
            'data' => $decodedResponse
        ];
    }
}

// Create global API client instance
$apiClient = new ApiClient();
?>