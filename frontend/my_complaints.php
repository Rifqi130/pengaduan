<?php
require_once 'includes/api_functions.php';
include 'includes/header.php';

if (!isMahasiswa()) { 
    header('Location: /pengaduan/frontend/index.php');
    exit;
}
?>

<h2 class="mb-4">Pengaduan Saya</h2>
<?php
$my_complaints = getComplaintsByUserId($_SESSION['user_id']);

if (count($my_complaints) > 0) {
    ?>
    <div class="list-group">
        <?php
        foreach ($my_complaints as $complaint) {
            ?>
            <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>"
                class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?php echo htmlspecialchars($complaint['title']); ?></h5>
                    <small><?php echo formatDate($complaint['createdAt'] ?? $complaint['date_posted']); ?></small>
                </div>
                <p class="mb-1"><strong>Kategori:</strong> <?php 
                    $kategori = $complaint['category'];
                    if (is_array($kategori)) {
                        echo htmlspecialchars($kategori['name'] ?? json_encode($kategori));
                    } else {
                        echo htmlspecialchars($kategori);
                    }
                ?></p>
                <p class="mb-1"><strong>Status:</strong> <span
                        class="badge <?php echo getStatusColor($complaint['status']); ?>"><?php echo htmlspecialchars($complaint['status']); ?></span>
                </p>
                <small>Tipe: <?php echo htmlspecialchars($complaint['tipe_aduan']); ?> - Klik untuk detail & balas.</small>
            </a>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    ?>
    <div class="alert alert-info">Anda belum memiliki pengaduan. <a href="index.php">Buat pengaduan baru?</a></div>
    <?php
}
?>
<div class="mt-3"><a href="index.php" class="btn btn-primary">Buat Pengaduan Baru</a></div>

<?php
include 'includes/footer.php';
?>