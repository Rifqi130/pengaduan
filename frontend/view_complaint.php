<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />';
echo '<div class="container mt-4">';

$complaint_id = $_GET['id'] ?? null;

if ($complaint_id) {
    $current_user_id = $_SESSION['user_id'] ?? null;
    $complaint = getComplaintById($complaint_id);

    if ($complaint) {
?>
        <div class="card shadow-sm">
            <div class="card-header"><h2>Detail Pengaduan: <?php echo htmlspecialchars($complaint['title']); ?></h2></div>
            <div class="card-body">
                <p><strong>Pelapor:</strong> <?php echo htmlspecialchars(!empty($complaint['nama_pelapor']) ? $complaint['nama_pelapor'] : 'Anonim'); ?> (<?php echo htmlspecialchars(!empty($complaint['email_pelapor']) ? $complaint['email_pelapor'] : 'Tidak Ada'); ?>)</p>
<?php       if(!empty($complaint['nim'])) { ?>
                <p><strong>NIM:</strong> <?php echo htmlspecialchars($complaint['nim']); ?></p>
<?php       } ?>
<?php       if(!empty($complaint['whatsapp'])) { ?>
                <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($complaint['whatsapp']); ?></p>
<?php       } ?>
<?php       if(!empty($complaint['jenis_kelamin'])) { ?>
                <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($complaint['jenis_kelamin']); ?></p>
<?php       } ?>
                <p><strong>Tanggal Kejadian:</strong> <?php echo htmlspecialchars($complaint['tanggal_kejadian']); ?></p>
                <p><strong>Lokasi Kejadian:</strong> <?php echo htmlspecialchars($complaint['lokasi_kejadian']); ?></p>
                <p><strong>Kategori:</strong> <?php echo htmlspecialchars(strval($complaint['category']['name'])); ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo getStatusColor($complaint['status']); ?>"><?php echo htmlspecialchars($complaint['status']); ?></span></p>
                <p><strong>Tipe Aduan:</strong> <?php echo htmlspecialchars($complaint['tipe_aduan']); ?></p>
                <p><strong>Deskripsi:</strong><br><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
<?php       if ($complaint['lampiran']) { ?>
                <div class="mb-3">
                    <strong>Lampiran:</strong>
                    <div class="mt-2">
                        <?php 
                        $filename = $complaint['lampiran'];
                        $fileUrl = getFileUrl($filename);
                        
                        if (isImageFile($filename)): ?>
                            <!-- Display image -->
                            <div class="card" style="max-width: 500px;">
                                <img src="<?php echo htmlspecialchars($fileUrl); ?>" 
                                     class="card-img-top" 
                                     alt="Lampiran"
                                     style="max-height: 400px; object-fit: contain;"
                                     onclick="openImageModal('<?php echo htmlspecialchars($fileUrl); ?>')">
                                <div class="card-body p-2">
                                    <small class="text-muted"><?php echo htmlspecialchars($filename); ?></small>
                                    <br>
                                    <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Buka di tab baru
                                    </a>
                                    <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                       download="<?php echo htmlspecialchars($filename); ?>"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                            
                        <?php elseif (isPdfFile($filename)): ?>
                            <!-- Display PDF -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        <?php echo htmlspecialchars($filename); ?>
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i> Lihat PDF
                                        </a>
                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                           download="<?php echo htmlspecialchars($filename); ?>"
                                           class="btn btn-outline-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif (isDocumentFile($filename)): ?>
                            <!-- Display Document -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-file-word text-primary"></i>
                                        <?php echo htmlspecialchars($filename); ?>
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                           target="_blank" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Buka
                                        </a>
                                        <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                           download="<?php echo htmlspecialchars($filename); ?>"
                                           class="btn btn-outline-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Display other files -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-file"></i>
                                        <?php echo htmlspecialchars($filename); ?>
                                    </h6>
                                    <a href="<?php echo htmlspecialchars($fileUrl); ?>" 
                                       download="<?php echo htmlspecialchars($filename); ?>"
                                       class="btn btn-outline-success">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
<?php       } ?>

                <p><strong>Tanggal Diajukan:</strong> <?php echo htmlspecialchars($complaint['date_posted']); ?></p>

<?php       if (isAdmin()) { ?>
                 <div class="mt-4">
                    <h4>Update Status Pengaduan</h4>
                    <form method="post"  >
                        <div class="form-group mb-3">
                            <label for="status">Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="Baru" <?php echo ($complaint['status'] == 'Baru') ? 'selected' : ''; ?>>Baru</option>
                                <option value="Diproses" <?php echo ($complaint['status'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                                <option value="Selesai" <?php echo ($complaint['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="Ditolak" <?php echo ($complaint['status'] == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update">Update Status</button>
                    </form>
                </div>

<?php       } ?>

                <div class="alert alert-info mt-4">
                    <strong>Catatan:</strong> Sistem ini menggunakan versi yang disederhanakan tanpa fitur balasan.
                    Status pengaduan akan diperbarui oleh admin melalui dashboard admin.
                </div>
            </div>
        </div>
<?php

    } else {
        echo '<div class="alert alert-warning">Pengaduan tidak ditemukan atau Anda tidak memiliki izin untuk melihatnya.</div>';
    }
} else {
    echo '<div class="alert alert-danger">ID Pengaduan tidak valid atau tidak disediakan.</div>';
}
?>

<div class="mt-4">
<?php
if (isMahasiswa() && isset($_SESSION['user_id'])) {
?>
    <a href="my_complaints.php" class="btn btn-secondary me-2">Kembali ke Pengaduan Saya</a>
<?php
}
?>
<?php
if (isAdmin()) {
?>
    <a href="complaints.php" class="btn btn-secondary me-2">Kembali ke Daftar Pengaduan</a>
<?php
}
?>
    <a href="public_complaints.php" class="btn btn-info me-2">Lihat Semua Pengaduan Publik</a>
    <a href="index.php" class="btn btn-primary">Buat Pengaduan Baru</a>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update']) && isAdmin()) {
    $status = $_POST['status'];
    if (updateComplaintStatus($complaint_id, $status)) {
        echo '<div class="alert alert-success">Status pengaduan berhasil diperbarui.</div>';
        echo "<script>window.location.href='" . $_SERVER['REQUEST_URI'] . "';</script>";
        exit;
    } else {
        echo '<div class="alert alert-danger">Gagal memperbarui status pengaduan.</div>';
    }
}

include 'includes/footer.php';
?>

<!-- Modal for image preview -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Preview">
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
