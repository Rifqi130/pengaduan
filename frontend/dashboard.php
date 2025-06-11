<?php
require_once 'includes/api_functions.php';

if (!isAdmin()) {
    header('Location: /');
    exit();
}

include 'includes/header.php';

// Get dashboard statistics
$stats = getDashboardStats();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                Dashboard Admin
            </h2>
            <span class="badge bg-primary fs-6">
                <i class="fas fa-clock me-1"></i>
                <?php echo date('d M Y, H:i'); ?>
            </span>
        </div>
    </div>
</div>

<?php if ($stats): ?>
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-clipboard-list fa-3x text-primary"></i>
                    </div>
                    <h3 class="card-title text-primary"><?php echo $stats['total_complaints'] ?? 0; ?></h3>
                    <p class="card-text text-muted">Total Pengaduan</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card warning h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-clock fa-3x text-warning"></i>
                    </div>
                    <h3 class="card-title text-warning"><?php echo $stats['complaints_by_status']['Baru'] ?? 0; ?></h3>
                    <p class="card-text text-muted">Pengaduan Baru</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card info h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-cog fa-3x text-info"></i>
                    </div>
                    <h3 class="card-title text-info"><?php echo $stats['complaints_by_status']['Diproses'] ?? 0; ?></h3>
                    <p class="card-text text-muted">Sedang Diproses</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card success h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="card-title text-success"><?php echo $stats['complaints_by_status']['Selesai'] ?? 0; ?></h3>
                    <p class="card-text text-muted">Selesai</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Statistik Pengguna
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-4x text-secondary"></i>
                    </div>
                    <h2 class="text-secondary"><?php echo $stats['total_users'] ?? 0; ?></h2>
                    <p class="text-muted">Total Pengguna Terdaftar</p>
                    <a href="users.php" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>Kelola Pengguna
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Pengaduan per Kategori
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($stats['complaints_by_category'] as $category => $count): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-medium"><?php echo htmlspecialchars($category); ?></span>
                            <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo ($stats['total_complaints'] > 0) ? ($count / $stats['total_complaints'] * 100) : 0; ?>%">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Pengaduan Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['recent_complaints'])): ?>
                        <?php foreach ($stats['recent_complaints'] as $complaint): ?>
                            <div class="border-start border-primary border-4 ps-3 mb-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($complaint['title']); ?></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo formatDate($complaint['date_posted']); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo htmlspecialchars($complaint['category']['name']); ?>
                                        </small>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="badge <?php echo getStatusColor($complaint['status']); ?>">
                                            <?php echo htmlspecialchars($complaint['status']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Lihat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada pengaduan terbaru</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="mb-4 text-center">
                        <i class="fas fa-tools me-2"></i>
                        Menu Admin
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="complaints.php" class="btn btn-primary btn-lg w-100 h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="fas fa-list fa-2x mb-2"></i><br>
                                    <span>Kelola Pengaduan</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="users.php" class="btn btn-info btn-lg w-100 h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i><br>
                                    <span>Kelola Pengguna</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="public_complaints.php" class="btn btn-success btn-lg w-100 h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <i class="fas fa-eye fa-2x mb-2"></i><br>
                                    <span>Pengaduan Publik</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                    <div>
                        <h4 class="alert-heading">Tidak dapat memuat statistik</h4>
                        <p class="mb-0">Terjadi kesalahan saat mengambil data dari server. Pastikan server backend sedang berjalan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
