<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

$register_error = null;

if (isLoggedIn()) {
    header('Location: /pengaduan/frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    if (!empty($username) && !empty($password) && !empty($email)) {
        $registration_result = registerUser($username, $password, $email);
        if ($registration_result === true) {
            header('Location: login.php?register_success=1');
            exit;
        } else {
            $register_error = $registration_result;
        }
    } else {
        $register_error = "Semua field wajib diisi.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4 text-center">Registrasi Mahasiswa</h2>
        
        <?php if (isset($register_error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($register_error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php" class="card p-4 shadow-sm" id="registerForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>

        <!-- Bootstrap Toast for validation -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
          <div id="registerToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body" id="toastMsg">
                <!-- Message will be set by JS -->
              </div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        </div>

        <script>
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            let msg = '';
            if (!username) {
                msg = 'Username wajib diisi.';
            } else if (!email) {
                msg = 'Email wajib diisi.';
            } else if (!password) {
                msg = 'Password wajib diisi.';
            } else if (password.length < 6) {
                msg = 'Password minimal 6 karakter.';
            } else if (!confirmPassword) {
                msg = 'Konfirmasi password wajib diisi.';
            } else if (password !== confirmPassword) {
                msg = 'Password dan konfirmasi password tidak cocok.';
            }
            if (msg) {
                event.preventDefault();
                document.getElementById('toastMsg').textContent = msg;
                var toast = new bootstrap.Toast(document.getElementById('registerToast'));
                toast.show();
            }
        });
        </script>
        
        <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        <p class="mt-3 text-center"><a href="index.php">Kembali ke Form Pengaduan</a></p>
    </div>
</div>

<?php

include 'includes/footer.php';
?>
