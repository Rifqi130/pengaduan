<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

if (isLoggedIn()) {
    header('Location: /pengaduan/frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $registrationResult = registerAdmin($username, $password, $email);

    if ($registrationResult === true) {
        echo '<div class="alert alert-success">Registrasi berhasil! Silakan <a href="admin-login.php">login</a>.</div>';
    } else {
        $error = $registrationResult;
        echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2>Register Admin</h2>
            <form method="post" id="adminRegisterForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
                <div>
                    <a href="admin-login.php">Klik untuk login</a>
                </div>
            </form>
        </div>
    </div>
</div>

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
    document.getElementById('adminRegisterForm').addEventListener('submit', function(event) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const confirm_password = document.getElementById('confirm_password').value.trim();
        let msg = '';
        if (!username) {
            msg = 'Username wajib diisi.';
        } else if (!email) {
            msg = 'Email wajib diisi.';
        } else if (!password) {
            msg = 'Password wajib diisi.';
        } else if (password.length < 6) {
            msg = 'Password minimal 6 karakter.';
        } else if (password !== confirm_password) {
            msg = 'Password dan Konfirmasi Password tidak cocok.';
        }
        if (msg) {
            event.preventDefault();
            document.getElementById('toastMsg').textContent = msg;
            var toast = new bootstrap.Toast(document.getElementById('registerToast'));
            toast.show();
        }
    });
</script>

<?php
include 'includes/footer.php';
?>