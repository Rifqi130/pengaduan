<?php
require_once 'includes/api_functions.php';

if (!isAdmin()) {
    header('Location: /pengaduan/frontend/index.php');
    exit();
}

include 'includes/header.php';

// Get filter parameters from URL
$statusFilter = $_GET['status'] ?? null;
$categoryFilter = $_GET['category'] ?? null;

// Build filter array
$filters = [];
if ($statusFilter) {
    $filters['status'] = $statusFilter;
}
if ($categoryFilter) {
    $filters['category'] = $categoryFilter;
}

// Get complaints with or without filters
$complaints = empty($filters) ? getAllComplaints() : getComplaintsWithFilters($filters);

$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

if ($action == 'edit' && $id) {
    $complaint = getComplaintById($id);
    if ($complaint) {
        ?>
        <div class="container mt-4">
            <h2>Edit Complaint Status</h2>
            <form method="post" action="complaints.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($complaint['id']); ?>">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Baru" <?php echo ($complaint['status'] == 'Baru') ? 'selected' : ''; ?>>Baru
                        </option>
                        <option value="Diproses" <?php echo ($complaint['status'] == 'Diproses') ? 'selected' : ''; ?>>
                            Diproses
                        </option>
                        <option value="Selesai" <?php echo ($complaint['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai
                        </option>
                        <option value="Ditolak" <?php echo ($complaint['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="update">Update Status</button>
                <a href="complaints.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        <?php
    } else {
        ?>
        <div class="container mt-4">
            <div class="alert alert-danger">Complaint not found.</div>
        </div>
        <?php
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $id = $_POST['id'];
        $status = $_POST['status'];
        if (updateComplaintStatus($id, $status)) {
            ?>
            <div class="container mt-4">
                <div class="alert alert-success">Complaint status updated successfully.</div>
            </div>
            <?php
            header("Refresh:0");
        } else {
            ?>
            <div class="container mt-4">
                <div class="alert alert-danger">Failed to update complaint status.</div>
            </div>
            <?php
        }
    }
    ?>
    <div class="container mt-4">
        <h2>Complaints Management</h2>

        <?php if ($statusFilter || $categoryFilter): ?>
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>Filtered by:</strong>
                    <?php if ($statusFilter): ?>
                        Status: <span
                            class="badge <?php echo getStatusColor($statusFilter); ?>"><?php echo htmlspecialchars($statusFilter); ?></span>
                    <?php endif; ?>
                    <?php if ($categoryFilter): ?>
                        Category: <span class="badge bg-secondary"><?php echo htmlspecialchars($categoryFilter); ?></span>
                    <?php endif; ?>
                </div>
                <a href="complaints.php" class="btn btn-outline-secondary btn-sm">Show All</a>
            </div>
        <?php endif; ?>

        <?php if (count($complaints) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($complaint['id']); ?></td>
                            <td><a
                                    href="view_complaint.php?id=<?php echo $complaint['id']; ?>"><?php echo htmlspecialchars($complaint['title']); ?></a>
                            </td>
                            <td>
                                <?php
                                echo htmlspecialchars($complaint['category']['name']);
                                ?>
                            </td>
                            <td><span
                                    class="badge <?php echo getStatusColor($complaint['status']); ?>"><?php echo htmlspecialchars($complaint['status']); ?></span>
                            </td>
                            <td><?php echo formatDate($complaint['createdAt'] ?? $complaint['date_posted']); ?></td>
                            <td>
                                <a href="complaints.php?action=edit&id=<?php echo $complaint['id']; ?>"
                                    class="btn btn-sm btn-primary">Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="deleteComplaint(<?php echo $complaint['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No complaints found.</div>
        <?php endif; ?>
    </div>
    <?php
}
?>

<script>
    function deleteComplaint(complaintId) {
        if (!confirm('Are you sure you want to delete this complaint? This action cannot be undone.')) {
            return;
        }

        // Disable the delete button to prevent multiple clicks
        const deleteButton = event.target;
        const originalText = deleteButton.textContent;
        deleteButton.disabled = true;
        deleteButton.textContent = 'Deleting...';

        // Make AJAX request
        fetch('delete_complaint_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                complaint_id: complaintId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message);

                    // Remove the row from the table
                    const row = deleteButton.closest('tr');
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();

                            // Check if table is now empty
                            const tbody = document.querySelector('table tbody');
                            if (tbody && tbody.children.length === 0) {
                                location.reload(); // Reload to show "No complaints found" message
                            }
                        }, 300);
                    }
                } else {
                    // Show error message
                    showAlert('danger', data.message);

                    // Re-enable the button
                    deleteButton.disabled = false;
                    deleteButton.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while deleting the complaint.');

                // Re-enable the button
                deleteButton.disabled = false;
                deleteButton.textContent = originalText;
            });
    }

    function showAlert(type, message) {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.maxWidth = '400px';

        alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

        // Add to page
        document.body.appendChild(alert);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
</script>

<?php
include 'includes/footer.php';
?>