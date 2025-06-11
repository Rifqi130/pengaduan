<?php
require_once 'includes/api_functions.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is admin
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Admin privileges required.'
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. POST required.'
    ]);
    exit;
}

// Get complaint ID from POST data
$input = json_decode(file_get_contents('php://input'), true);
$complaint_id = $input['complaint_id'] ?? null;

if (!$complaint_id || !is_numeric($complaint_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid complaint ID provided.'
    ]);
    exit;
}

// Attempt to delete the complaint
try {
    $result = deleteComplaint($complaint_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Complaint deleted successfully.',
            'complaint_id' => (int)$complaint_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete complaint. It may not exist or you may not have permission.'
        ]);
    }
} catch (Exception $e) {
    error_log("Delete complaint error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the complaint.'
    ]);
}
?>