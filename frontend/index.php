<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

// Handle new complaint submission (can be anonymous or logged-in user)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sertakan_data_diri = isset($_POST['sertakan_data_diri']);
    $nama_pelapor = $sertakan_data_diri ? trim($_POST['nama_pelapor'] ?? '') : '';
    $jenis_kelamin = $sertakan_data_diri ? $_POST['jenis_kelamin'] ?? '' : '';
    $nim = $sertakan_data_diri ? trim($_POST['nim'] ?? '') : '';
    $whatsapp = $sertakan_data_diri ? trim($_POST['whatsapp'] ?? '') : '';
    $email_pelapor = $sertakan_data_diri ? trim($_POST['email_pelapor'] ?? '') : '';
    $tanggal_kejadian = $_POST['tanggal_kejadian'] ?? '';
    $lokasi_kejadian = trim($_POST['lokasi_kejadian'] ?? '');
    $title = trim($_POST['title'] ?? 'Pengaduan dari ' . $nama_pelapor);
    $description = trim($_POST['description'] ?? '');
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $tipe_aduan = $_POST['tipe_aduan'] ?? 'private';

    // Ambil daftar kategori dari database (tanpa di-lowercase)
    $categories = getCategories();
    
    // Debug untuk GCP deployment
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Available categories: " . json_encode($categories));
    }

    // Validasi kategori (case-insensitive)
    $category_valid = false;
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            $categoryName = is_array($cat) ? ($cat['name'] ?? '') : (string)$cat;
            if (strcasecmp($categoryName, $category) === 0) {
                $category_valid = true;
                break;
            }
        }
    }
    
    if (!$category_valid) {
        $submit_error_message = 'Gagal mengirim pengaduan: Invalid category. Available categories: ' . 
                               implode(', ', array_map(function($cat) {
                                   return is_array($cat) ? ($cat['name'] ?? '') : (string)$cat;
                               }, $categories));
    } else if (
        $tanggal_kejadian && $lokasi_kejadian && $title && $description && $category &&
        (!$sertakan_data_diri || ($sertakan_data_diri && $nama_pelapor && $email_pelapor))
    ) {
        $lampiran_filename = handleFileUpload('lampiran');
        $user_id_for_complaint = $_SESSION['user_id'] ?? null;

        $result = submitComplaint($user_id_for_complaint, $title, $description, $category, $nama_pelapor, $jenis_kelamin, $nim, $whatsapp, $email_pelapor, $tanggal_kejadian, $lokasi_kejadian, $lampiran_filename, $tipe_aduan);
        if ($result === true) {
            $submit_success_message = 'Pengaduan berhasil diajukan! Terima kasih.';
            header('Location: complaints.php');
            exit();
        } else {
            $submit_error_message = 'Gagal mengirim pengaduan: ' . $result;
        }
    } else {
        $submit_error_message = 'Mohon lengkapi semua field yang wajib diisi. ' .
            ($sertakan_data_diri ? 'Jika menyertakan data diri, Nama dan Email wajib diisi.' : '');
    }
}

if (isAdmin()):
    header('Location: /pengaduan/frontend/dashboard.php');
    exit();
else: ?>
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header text-center">
                    <h2 class="mb-0">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Formulir Pengaduan Mahasiswa
                    </h2>
                    <p class="text-muted mt-2 mb-0">Sampaikan keluhan atau saran Anda dengan mudah</p>
                </div>
                <div class="card-body">
                    <?php if (isset($submit_success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($submit_success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($submit_error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($submit_error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Judul Pengaduan
                                </label>
                                <input type="text" name="title" id="title" class="form-control" required 
                                       placeholder="Masukkan judul yang jelas dan singkat">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Deskripsi Lengkap Pengaduan
                            </label>
                            <textarea name="description" id="description" class="form-control" rows="6" required
                                      placeholder="Jelaskan detail pengaduan Anda dengan lengkap..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="tanggal_kejadian" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Tanggal Kejadian
                                </label>
                                <input type="date" name="tanggal_kejadian" id="tanggal_kejadian" class="form-control" required
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="lokasi_kejadian" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Lokasi Kejadian
                                </label>
                                <input type="text" name="lokasi_kejadian" id="lokasi_kejadian" class="form-control" required
                                       placeholder="Contoh: Gedung A Lantai 2">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="category" class="form-label">
                                    <i class="fas fa-tags me-1"></i>Kategori Pengaduan
                                </label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="">Pilih Kategori...</option>
                                    <?php
                                    $categories = getCategories();
                                    if (empty($categories)) {
                                        echo '<option value="" disabled>Tidak ada kategori tersedia - Periksa koneksi backend</option>';
                                    } else {
                                        foreach ($categories as $cat) {
                                            $categoryName = is_array($cat) ? ($cat['name'] ?? '') : (string)$cat;
                                            $categoryId = is_array($cat) ? ($cat['id'] ?? '') : '';
                                            if (!empty($categoryName)) {
                                                echo "<option value='" . htmlspecialchars($categoryName) . "' data-id='" . htmlspecialchars($categoryId) . "'>" . htmlspecialchars($categoryName) . "</option>";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="form-text">
                                    <small class="text-muted">
                                        Backend Status: 
                                        <span id="backend-status" class="badge bg-secondary">Checking...</span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="lampiran" class="form-label">
                                    <i class="fas fa-paperclip me-1"></i>Lampiran (Opsional)
                                </label>
                                <input type="file" name="lampiran" id="lampiran" class="form-control">
                                <div class="form-text">Format: JPG, PNG, PDF, DOC (Max: 5MB)</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-eye me-1"></i>Tipe Aduan
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="tipe_aduan" id="tipe_private"
                                            value="private" checked>
                                        <label class="form-check-label" for="tipe_private">
                                            <strong>Private</strong><br>
                                            <small class="text-muted">Hanya bisa dilihat Admin dan Anda</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="tipe_aduan" id="tipe_public"
                                            value="public">
                                        <label class="form-check-label" for="tipe_public">
                                            <strong>Public</strong><br>
                                            <small class="text-muted">Bisa dilihat semua orang</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check p-3 border rounded">
                                <input type="checkbox" class="form-check-input" id="sertakan_data_diri" name="sertakan_data_diri">
                                <label class="form-check-label" for="sertakan_data_diri">
                                    <strong>Sertakan Data Diri</strong><br>
                                    <small class="text-muted">Centang jika ingin menyertakan identitas Anda</small>
                                </label>
                            </div>
                        </div>

                        <div id="data_diri_fields" style="display: none;" class="border rounded p-4 mb-4 bg-light">
                            <h6 class="mb-3">
                                <i class="fas fa-user me-1"></i>Informasi Pribadi
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_pelapor" class="form-label">Nama Pelapor</label>
                                    <input type="text" name="nama_pelapor" id="nama_pelapor" class="form-control"
                                           placeholder="Nama lengkap Anda">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nim" class="form-label">NIM Mahasiswa</label>
                                    <input type="text" name="nim" id="nim" class="form-control"
                                           placeholder="Nomor Induk Mahasiswa">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="whatsapp" class="form-label">Nomor WhatsApp</label>
                                    <input type="tel" name="whatsapp" id="whatsapp" class="form-control"
                                           placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email_pelapor" class="form-label">Email Aktif</label>
                                <input type="email" name="email_pelapor" id="email_pelapor" class="form-control"
                                       placeholder="email@example.com">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Pengaduan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        Menu Lainnya
                    </h5>
                    <div class="row justify-content-center">
                        <div class="col-md-3 mb-2">
                            <a href="login.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-sign-in-alt me-1"></i>Login Mahasiswa
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="public_complaints.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-eye me-1"></i>Pengaduan Publik
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="admin-login.php" class="btn btn-outline-danger w-100">
                                <i class="fas fa-user-shield me-1"></i>Login Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Toast for validation -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="complaintToast" class="toast align-items-center text-bg-danger border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="complaintToastMsg">
                    <!-- Message will be set by JS -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Check backend status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkBackendStatus();
        });

        function checkBackendStatus() {
            const statusElement = document.getElementById('backend-status');
            
            fetch('<?php echo API_BASE_URL; ?>/health')
                .then(response => {
                    if (response.ok) {
                        statusElement.textContent = 'Connected';
                        statusElement.className = 'badge bg-success';
                    } else {
                        statusElement.textContent = 'Error';
                        statusElement.className = 'badge bg-danger';
                    }
                })
                .catch(error => {
                    statusElement.textContent = 'Offline';
                    statusElement.className = 'badge bg-danger';
                    console.error('Backend check failed:', error);
                });
        }

        document.getElementById('sertakan_data_diri').addEventListener('change', function () {
            document.getElementById('data_diri_fields').style.display = this.checked ? 'block' : 'none';
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            let title = document.getElementById('title').value.trim();
            let description = document.getElementById('description').value.trim();
            let tanggal_kejadian = document.getElementById('tanggal_kejadian').value;
            let lokasi_kejadian = document.getElementById('lokasi_kejadian').value.trim();
            let category = document.getElementById('category').value;
            let sertakanDataDiri = document.getElementById('sertakan_data_diri').checked;
            let nama_pelapor = document.getElementById('nama_pelapor').value.trim();
            let email_pelapor = document.getElementById('email_pelapor').value.trim();
            
            let msg = '';
            
            // Validasi field wajib
            if (!title) {
                msg = 'Judul pengaduan wajib diisi.';
            } else if (!description) {
                msg = 'Deskripsi pengaduan wajib diisi.';
            } else if (!tanggal_kejadian) {
                msg = 'Tanggal kejadian wajib diisi.';
            } else if (!lokasi_kejadian) {
                msg = 'Lokasi kejadian wajib diisi.';
            } else if (!category) {
                msg = 'Kategori pengaduan wajib dipilih.';
            } 
            // Validasi data pribadi jika checkbox dicentang
            else if (sertakanDataDiri) {
                if (!nama_pelapor) {
                    msg = 'Nama pelapor wajib diisi jika menyertakan data diri.';
                } else if (!email_pelapor) {
                    msg = 'Email pelapor wajib diisi jika menyertakan data diri.';
                } else if (email_pelapor) {
                    // Basic email format validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email_pelapor)) {
                        msg = 'Format email tidak valid.';
                    }
                }
            }
            
            if (msg) {
                event.preventDefault();
                document.getElementById('complaintToastMsg').textContent = msg;
                var toast = new bootstrap.Toast(document.getElementById('complaintToast'));
                toast.show();
                return;
            }
        });
    </script>

<?php endif;
include 'includes/footer.php'; ?>