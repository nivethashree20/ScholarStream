<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch paper stats
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END) as declined,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Revision Required' THEN 1 ELSE 0 END) as revision
FROM research_papers rp
LEFT JOIN paper_coauthors pc ON rp.id = pc.paper_id
WHERE rp.student_id = ? OR pc.student_email = (SELECT email FROM users WHERE id = ?)");
$stmt->execute([$student_id, $student_id]);
$stats = $stmt->fetch();

// Daily Submissions Chart Data (Last 7 Days)
$chart_dates = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[$date] = 0;
}

$seven_days_ago = date('Y-m-d', strtotime('-7 days'));
$stmt = $pdo->prepare("SELECT DATE(rp.submitted_at) as submit_date, COUNT(DISTINCT rp.id) as count 
    FROM research_papers rp
    LEFT JOIN paper_coauthors pc ON rp.id = pc.paper_id
    WHERE (rp.student_id = ? OR pc.student_email = (SELECT email FROM users WHERE id = ?))
    AND rp.submitted_at >= ? 
    GROUP BY DATE(rp.submitted_at)");
$stmt->execute([$student_id, $student_id, $seven_days_ago]);
$daily_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($daily_data as $row) {
    // If the database returns NULL for submit_date due to some reason, skip it
    if ($row['submit_date'] && isset($chart_dates[$row['submit_date']])) {
        $chart_dates[$row['submit_date']] = (int)$row['count'];
    }
}

$labels = array_keys($chart_dates);
$formatted_labels = array_map(function($date) {
    return date('M j', strtotime($date));
}, $labels);

$chart_labels_json = json_encode($formatted_labels);
$chart_data_json = json_encode(array_values($chart_dates));

// Fetch paper list (Own papers + Co-authored papers)
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$stmt = $pdo->prepare("SELECT DISTINCT rp.* 
    FROM research_papers rp 
    LEFT JOIN paper_coauthors pc ON rp.id = pc.paper_id 
    WHERE (rp.student_id = ? OR pc.student_email = (SELECT email FROM users WHERE id = ?)) 
    AND (rp.title LIKE ?) 
    ORDER BY rp.submitted_at DESC");
$stmt->execute([$student_id, $student_id, $search]);
$papers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.6">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="mb-5">
            <h1 class="fw-bold" style="color: var(--primary); font-size: 2.5rem; letter-spacing: -0.5px;">Research Paper Details</h1>
            <p class="text-muted fs-5 opacity-75">View and manage your submitted research papers.</p>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0 fw-semibold">Total Research Papers</h6>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;"><?php echo $stats['total'] ?? 0; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0 fw-semibold">Approved</h6>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00c853" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;"><?php echo $stats['approved'] ?? 0; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted mb-0 fw-semibold">Pending</h6>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <h1 class="fw-bold mb-0" style="font-size: 3rem;"><?php echo $stats['pending'] ?? 0; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm border-warning border-opacity-25" style="background: rgba(255, 193, 7, 0.05);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-warning mb-0 fw-semibold">Revisions Needed</h6>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </div>
                    <h1 class="fw-bold mb-0 text-warning" style="font-size: 3rem;"><?php echo $stats['revision'] ?? 0; ?></h1>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-4 h-100 shadow-sm border-danger border-opacity-25" style="background: rgba(255, 82, 82, 0.05);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-danger mb-0 fw-semibold">Declined</h6>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff5252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                    <h1 class="fw-bold mb-0 text-danger" style="font-size: 3rem;"><?php echo $stats['declined'] ?? 0; ?></h1>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <div class="card p-4 shadow-sm border-0" style="background: rgba(255,255,255,0.03);">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-graph-up me-2" style="color: var(--primary);"></i>Your Daily Progress (Last 7 Days)</h5>
                    </div>
                    <div style="height: 300px; width: 100%;">
                        <canvas id="studentProgressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4" style="background-color: #1e1e2e; border: 1px solid rgba(255,255,255,0.05);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form action="" method="GET" class="position-relative" style="max-width: 400px; width: 100%;">
                    <svg width="20" height="20" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.7);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" class="form-control ps-5 text-white" placeholder="Search by title..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                </form>
                <a href="submit_paper.php" class="btn btn-primary d-flex align-items-center shadow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Register New Paper
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" style="background: transparent !important; color: inherit;">
                    <thead class="small" style="color: #ffffff; background: rgba(255,255,255,0.02);">
                        <tr>
                            <th class="border-0 ps-3 fw-normal">Research Title</th>
                            <th class="border-0 fw-normal">Research Area</th>
                            <th class="border-0 fw-normal">Status</th>
                            <th class="border-0 text-end pe-3 fw-normal">Action</th>
                        </tr>
                    </thead>
                <style>
                    .clickable-row { cursor: pointer; transition: background-color 0.2s; }
                    .clickable-row:hover { background-color: rgba(255, 255, 255, 0.05) !important; }
                </style>
                <tbody>
                    <?php if (empty($papers)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center opacity-75">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-3 text-muted"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    <h5 class="text-muted fw-normal mb-0">You haven't submitted any papers yet.</h5>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($papers as $paper): ?>
                            <tr class="clickable-row" onclick="window.location='view_paper.php?id=<?php echo $paper['id']; ?>'">
                                    <td class="ps-3 border-bottom-0">
                                        <span class="fw-medium text-white"><?php echo htmlspecialchars($paper['title']); ?></span>
                                    </td>
                                    <td class="border-bottom-0">
                                        <span class="text-white-50 small"><?php echo htmlspecialchars($paper['research_area']); ?></span>
                                    </td>
                                    <td class="border-bottom-0">
                                        <?php 
                                            $badge_class = 'bg-secondary';
                                            if($paper['status'] == 'Approved') $badge_class = 'bg-success';
                                            if($paper['status'] == 'Declined') $badge_class = 'bg-danger';
                                            if($paper['status'] == 'Pending') $badge_class = 'bg-warning text-dark';
                                            if($paper['status'] == 'Revision Required') $badge_class = 'bg-info text-dark';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> rounded-pill small">
                                            <?php echo $paper['status']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 border-bottom-0">
                                        <?php if ($paper['status'] == 'Approved'): ?>
                                            <a href="certificate.php?id=<?php echo $paper['id']; ?>" target="_blank" class="btn btn-sm btn-success me-2">
                                                <i class="bi bi-award me-1"></i> Certificate
                                            </a>
                                        <?php endif; ?>
                                        <a href="view_paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-sm btn-outline-light opacity-75">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('studentProgressChart').getContext('2d');
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(187, 134, 252, 0.4)'); // Student primary color
        gradient.addColorStop(1, 'rgba(187, 134, 252, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $chart_labels_json; ?>,
                datasets: [{
                    label: 'Papers Submitted',
                    data: <?php echo $chart_data_json; ?>,
                    borderColor: '#bb86fc', // --primary
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#1e1e2e',
                    pointBorderColor: '#bb86fc',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#2d2d42',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) { return context.parsed.y + ' submissions'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'rgba(255, 255, 255, 0.5)', precision: 0, stepSize: 1 },
                        grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false }
                    },
                    x: {
                        ticks: { color: 'rgba(255, 255, 255, 0.5)' },
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
