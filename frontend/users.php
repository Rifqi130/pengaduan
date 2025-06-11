<?php
require_once 'includes/api_functions.php';

if (!isAdmin()) {
    header('Location: /pengaduan/frontend/index.php');
    exit();
}

include 'includes/header.php';

// Get filter parameters from URL
$roleFilter = $_GET['role'] ?? null;

$delete_message = null;
$update_message = null;

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (deleteUser($id)) {
        $delete_message = '<div class="alert alert-success">User deleted successfully.</div>';
    } else {
        $delete_message = '<div class="alert alert-danger">Failed to delete user.</div>';
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'update_status' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? true : false;
    if (updateUserStatus($id, $is_active)) {
        $update_message = '<div class="alert alert-success">User status updated successfully.</div>';
    } else {
        $update_message = '<div class="alert alert-danger">Failed to update user status.</div>';
    }
}

$users = getAllUsers();

?>

<div class="container mt-4">
    <h2>User Management</h2>
    
    <?php if ($roleFilter): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Filtered by:</strong>
                Role: <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($roleFilter)); ?></span>
            </div>
            <a href="users.php" class="btn btn-outline-secondary btn-sm">Show All Users</a>
        </div>
    <?php endif; ?>
    
    <?php if ($delete_message): ?>
        <?php echo $delete_message; ?>
    <?php endif; ?>

    <?php if ($update_message): ?>
        <?php echo $update_message; ?>
    <?php endif; ?>

    <?php if (count($users) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Full Name</th>
                    <th>Created At</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <form method="POST" action="users.php" class="d-inline">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active_<?php echo $user['id']; ?>" name="is_active" value="1" <?php echo ($user['is_active'] ? 'checked' : ''); ?> <?php echo ($user['role'] === 'admin' ? 'disabled' : ''); ?>>
                                    <label class="form-check-label" for="is_active_<?php echo $user['id']; ?>">Active</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary" <?php echo ($user['role'] === 'admin' ? 'disabled' : ''); ?>>Update</button>
                            </form>
                        </td>
                        <td>
                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No users found.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>