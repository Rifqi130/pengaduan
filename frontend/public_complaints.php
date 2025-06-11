<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';
if (isAdmin()) {
    header('Location: /pengaduan/frontend/complaints.php');
    exit;
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Pengaduan Publik</h2>
    <?php
    $public_complaints = getAllPublicComplaints();

    if (count($public_complaints) > 0):
        ?>
        <div class="list-group">
            <?php foreach ($public_complaints as $complaint): ?>
                <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                    class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($complaint['title'] ?? 'No Title'); ?></h5>
                        <small><?php echo formatDate($complaint['date_posted'] ?? $complaint['createdAt'] ?? ''); ?></small>
                    </div>
                    <p class="mb-1"><strong>Pelapor:</strong>
                        <?php echo htmlspecialchars($complaint['nama_pelapor'] ?? 'Anonim'); ?></p>
                    <p class="mb-1"><strong>Kategori:</strong>
                        <?php echo htmlspecialchars($complaint['category']['name'] ?? $complaint['category'] ?? 'Unknown'); ?>
                    </p>
                    <p class="mb-1"><strong>Status:</strong> <span
                            class="badge <?php echo getStatusColor($complaint['status'] ?? 'Unknown'); ?>"><?php echo htmlspecialchars($complaint['status'] ?? 'Unknown'); ?></span>
                    </p>
                    <small>Klik untuk detail.</small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Belum ada pengaduan publik saat ini.</div>
    <?php endif; ?>
    <div class="mt-3"><a href="index.php" class="btn btn-primary">Buat Pengaduan Baru</a></div>
</div>

<?php
include 'includes/footer.php';
?>