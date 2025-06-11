<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

$login_error = null;
$register_message = $_GET['register_success'] ?? null; // Check for registration success message

if (isLoggedIn()) { 
    header('Location: /pengaduan/frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (loginUser($username, $password, 'mahasiswa')) {
        header('Location: my_complaints.php');
        exit;
    } else {
        $login_error = "Username atau password salah.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4 text-center">Login Mahasiswa</h2>

        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

        <?php if (isset($register_message)): ?>
            <div class="alert alert-success">Registrasi berhasil. Silakan login.</div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Registrasi di sini</a></p>
        <p class="mt-3 text-center"><a href="index.php">Kembali ke Form Pengaduan</a></p>
    </div>
</div>

<?php

include 'includes/footer.php';
?>