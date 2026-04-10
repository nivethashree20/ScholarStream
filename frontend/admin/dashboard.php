<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch global stats
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END) as declined,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Revision Required' THEN 1 ELSE 0 END) as revision
FROM research_papers");
$stats = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.2">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="mb-5">
            <h1 class="fw-bold text-primary">Admin Dashboard</h1>
            <p class="text-muted">Overview of all research papers submitted by students.</p>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm border-0" style="background: rgba(255,255,255,0.03);">
                    <h6 class="text-muted small mb-2 opacity-75">Total Submissions</h6>
                    <h2 class="fw-bold mb-0"><?php echo $stats['total'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-4 h-100 border-0" style="background: rgba(76, 175, 80, 0.1);">
                    <h6 class="text-success small mb-2">Approved</h6>
                    <h2 class="fw-bold mb-0 text-success"><?php echo $stats['approved'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-4 h-100 border-0" style="background: rgba(255, 193, 7, 0.1);">
                    <h6 class="text-warning small mb-2">Pending</h6>
                    <h2 class="fw-bold mb-0 text-warning"><?php echo $stats['pending'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-4 h-100 border-0" style="background: rgba(255, 152, 0, 0.15);">
                    <h6 class="text-warning small mb-2" style="color: #ff9800 !important;">Revision</h6>
                    <h2 class="fw-bold mb-0" style="color: #ff9800;"><?php echo $stats['revision'] ?? 0; ?></h2>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card p-4 h-100 border-0" style="background: rgba(244, 67, 54, 0.1);">
                    <h6 class="text-danger small mb-2">Declined</h6>
                    <h2 class="fw-bold mb-0 text-danger"><?php echo $stats['declined'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
