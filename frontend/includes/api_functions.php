<?php
session_start();

require_once 'api_client.php';

function sanitizeData($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getStatusColor($status)
{
    switch ($status) {
        case 'Baru':
            return 'bg-secondary text-white';
        case 'Diproses':
            return 'bg-info text-white';
        case 'Selesai':
            return 'bg-success text-white';
        case 'Ditolak':
            return 'bg-danger text-white';
        default:
            return 'bg-light text-dark';
    }
}

function handleFileUpload($input_name)
{
    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$input_name];
        $filename = basename($file['name']);
        $upload_dir = 'uploads/complaints/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filepath = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        } else {
            error_log("File upload failed for: " . $filename);
            return null;
        }
    }
    return null;
}

function registerUser($username, $password, $email)
{
    global $apiClient;

    $username = sanitizeData($username);
    $email = sanitizeData($email);

    if (empty($username) || empty($password) || empty($email)) {
        return "Semua field wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid.";
    }

    $response = $apiClient->register($username, $email, $password);

    if ($response['status_code'] === 201) {
        return true;
    } else {
        $errorMessage = '';
        if (isset($response['data']['errors'])) {
            foreach ($response['data']['errors'] as $errors) {
                $errorMessage .= $errors['msg'] . '. ';
            }
            return trim($errorMessage);
        } else {
            $errorMessage = $response['data']['message'] ?? 'Registration failed';
            if (strpos($errorMessage, 'already exists') !== false || strpos($errorMessage, 'duplicate') !== false) {
                return "Username atau email sudah digunakan.";
            }
            return $errorMessage;
        }
    }
}

function loginUser($username, $password, $role = 'mahasiswa')
{
    global $apiClient;

    $username = sanitizeData($username);

    if (empty($username) || empty($password)) {
        return false;
    }

    $response = $apiClient->login($username, $password, $role);

    return $response['status_code'] === 200;
}

function loginAdmin($username, $password)
{
    return loginUser($username, $password, 'admin');
}

function registerAdmin($username, $password, $email)
{
    global $apiClient;

    $username = sanitizeData($username);
    $email = sanitizeData($email);

    if (empty($username) || empty($password) || empty($email)) {
        return "Semua field wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid.";
    }

    $response = $apiClient->registerAdmin($username, $email, $password);

    if ($response['status_code'] === 201) {
        return true;
    } else {
        $errorMessage = '';
        if (isset($response['data']['errors'])) {
            foreach ($response['data']['errors'] as $errors) {
                $errorMessage .= $errors['msg'] . '. ';
            }
            return trim($errorMessage);
        } else {
            $errorMessage = $response['data']['message'] ?? 'Registration failed';
            if (strpos($errorMessage, 'already exists') !== false || strpos($errorMessage, 'duplicate') !== false) {
                return "Username atau email sudah digunakan.";
            }
            return $errorMessage;
        }
    }
}

function logoutUser()
{
    global $apiClient;

    $apiClient->logout();
    session_unset();
    session_destroy();
}

function isLoggedIn()
{
    return isset($_SESSION['username']) && isset($_SESSION['jwt_token']);
}

function isMahasiswa()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'mahasiswa';
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function submitComplaint($user_id, $title, $description, $category, $nama_pelapor, $jenis_kelamin, $nim, $whatsapp, $email_pelapor, $tanggal_kejadian, $lokasi_kejadian, $lampiran_filename, $tipe_aduan)
{
    global $apiClient;

    $title = sanitizeData($title);
    $description = sanitizeData($description);
    $lokasi_kejadian = sanitizeData($lokasi_kejadian);
    $nama_pelapor = sanitizeData($nama_pelapor);
    $email_pelapor = sanitizeData($email_pelapor);
    $whatsapp = sanitizeData($whatsapp);
    $nim = sanitizeData($nim);

    // Prepare complaint data
    $complaintData = [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'tanggal_kejadian' => $tanggal_kejadian,
        'lokasi_kejadian' => $lokasi_kejadian,
        'tipe_aduan' => $tipe_aduan
    ];

    // Add personal data if provided
    if (!empty($nama_pelapor)) {
        $complaintData['sertakan_data_diri'] = true;
        $complaintData['nama_pelapor'] = $nama_pelapor;
        $complaintData['jenis_kelamin'] = $jenis_kelamin;
        $complaintData['nim'] = $nim;
        $complaintData['whatsapp'] = $whatsapp;
        $complaintData['email_pelapor'] = $email_pelapor;
    } else {
        $complaintData['sertakan_data_diri'] = false;
    }

    // Submit complaint with file if exists
    $response = $apiClient->submitComplaint($complaintData, $lampiran_filename);

    if ($response['status_code'] === 201) {
        return true;
    } else {
        // Handle specific error codes
        $errorData = $response['data'] ?? [];
        
        if (isset($errorData['code'])) {
            switch ($errorData['code']) {
                case 'ACCOUNT_INACTIVE':
                    return 'Akun Anda tidak aktif. Silakan hubungi admin untuk mengaktifkan akun.';
                case 'NAMA_REQUIRED':
                    return 'Nama pelapor wajib diisi jika menyertakan data diri.';
                case 'EMAIL_REQUIRED':
                    return 'Email pelapor wajib diisi jika menyertakan data diri.';
                case 'CATEGORY_REQUIRED':
                    return 'Kategori pengaduan wajib dipilih.';
                case 'INVALID_CATEGORY':
                    return 'Kategori yang dipilih tidak valid.';
                default:
                    return $errorData['message'] ?? 'Terjadi kesalahan tidak dikenal.';
            }
        }
        
        return $errorData['message'] ?? 'Unknown error';
    }
}

function getComplaintsByUserId($user_id)
{
    global $apiClient;

    $response = $apiClient->getCurrentUserComplaints();

    if ($response['status_code'] === 200) {
        return $response['data']['data']['complaints'] ?? [];
    }

    return [];
}

function getAllPublicComplaints()
{
    global $apiClient;

    $response = $apiClient->getComplaints(['type' => 'public']);

    if ($response['status_code'] === 200) {
        return $response['data']['data']['complaints'] ?? [];
    }

    return [];
}
function getComplaintById($complaint_id)
{
    global $apiClient;

    $response = $apiClient->getComplaintById($complaint_id);
    if ($response['status_code'] === 200) {
        return $response['data']['data'] ?? null;
    }

    return null;
}

function getAllComplaints()
{
    global $apiClient;

    $response = $apiClient->getComplaints();
    // echo "<pre>"; var_dump($response); echo "<pre>";
    

    if ($response['status_code'] === 200) {
        return $response['data']['data']['complaints'] ?? [];
    }

    return [];
}

function updateComplaintStatus($complaint_id, $status)
{
    global $apiClient;

    $response = $apiClient->updateComplaintStatus($complaint_id, $status);

    return $response['status_code'] === 200;
}

function getAllUsers()
{
    global $apiClient;

    $response = $apiClient->getAllUsers();

    if ($response['status_code'] === 200) {
        return $response['data']['data']['users'] ?? [];
    }

    return [];
}

function deleteUser($user_id)
{
    global $apiClient;

    $response = $apiClient->deleteUser($user_id);

    return $response['status_code'] === 200;
}

function updateUserStatus($user_id, $is_active)
{
    global $apiClient;

    $response = $apiClient->updateUserStatus($user_id, $is_active);

    return $response['status_code'] === 200;
}

function deleteComplaint($complaint_id)
{
    global $apiClient;

    $response = $apiClient->deleteComplaint($complaint_id);

    return $response['status_code'] === 200;
}

function getCategories()
{
    global $apiClient;

    try {
        $response = $apiClient->getCategories();

        if ($response['status_code'] === 200) {
            $categories = $response['data']['data']['categories'] ?? [];
            
            // Debug logging for GCP deployment
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Categories API Response: " . json_encode($categories));
            }
            
            // Jika API mengembalikan array kosong, fallback ke default
            if (empty($categories)) {
                return getDefaultCategories();
            }
            return $categories;
        }
    } catch (Exception $e) {
        error_log("Categories API Error: " . $e->getMessage());
    }

    // Fallback ke hardcoded jika API gagal
    return getDefaultCategories();
}

function getDefaultCategories()
{
    return [
        ['id' => 1, 'name' => 'Fasilitas', 'description' => 'Pengaduan terkait fasilitas kampus'],
        ['id' => 2, 'name' => 'Akademik', 'description' => 'Pengaduan terkait kegiatan akademik'],
        ['id' => 3, 'name' => 'Layanan', 'description' => 'Pengaduan terkait layanan kampus'],
        ['id' => 4, 'name' => 'Keuangan', 'description' => 'Pengaduan terkait keuangan dan pembayaran'],
        ['id' => 5, 'name' => 'Lainnya', 'description' => 'Pengaduan kategori lainnya']
    ];
}

function getDashboardStats()
{
    global $apiClient;

    $response = $apiClient->getDashboardStats();

    if ($response['status_code'] === 200) {
        return $response['data']['data'] ?? null;
    }

    return null;
}

// Helper function to handle API errors gracefully
function handleApiError($response, $defaultMessage = 'An error occurred')
{
    if (isset($response['data']['message'])) {
        return $response['data']['message'];
    }
    return $defaultMessage;
}

// Helper function to format date from API response
function formatDate($dateString)
{
    if (empty($dateString))
        return '';

    try {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $dateString;
    }
}

// Helper function to check if server is reachable
function isApiServerReachable()
{
    global $apiClient;

    $response = $apiClient->healthCheck();
    return $response['status_code'] === 200;
}

// Helper function to check if both servers are reachable
function areApiServersReachable()
{
    global $apiClient;

    $mysqlStatus = $apiClient->healthCheck();
    $pgStatus = $apiClient->getSystemAnalytics();
    
    return [
        'mysql' => $mysqlStatus['status_code'] === 200,
        'postgresql' => $pgStatus['status_code'] === 200
    ];
}

function getFileUrl($filename) {
    if (empty($filename)) {
        return null;
    }
    
    // Use the backend API file serving endpoint - Update for GCP
    $baseUrl = 'http://34.121.164.196:3000'; // Sesuaikan dengan URL backend GCP Anda
    return $baseUrl . '/api/files/attachment/' . urlencode($filename);
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function isImageFile($filename) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    return in_array(getFileExtension($filename), $imageExtensions);
}

function isPdfFile($filename) {
    return getFileExtension($filename) === 'pdf';
}

function isDocumentFile($filename) {
    $docExtensions = ['doc', 'docx'];
    return in_array(getFileExtension($filename), $docExtensions);
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function getComplaintAnalytics($complaintId = null)
{
    global $apiClient;

    $response = $apiClient->getComplaintLogs($complaintId);
    
    if ($response['status_code'] === 200) {
        return $response['data']['data']['logs'] ?? [];
    }
    
    return [];
}

function logComplaintSubmission($complaintId)
{
    global $apiClient;
    
    return $apiClient->logComplaintActivity(
        $complaintId, 
        'created', 
        'Complaint submitted successfully'
    );
}

function logComplaintStatusUpdate($complaintId, $newStatus)
{
    global $apiClient;
    
    return $apiClient->logComplaintActivity(
        $complaintId, 
        'status_updated', 
        "Status changed to: $newStatus"
    );
}
?>