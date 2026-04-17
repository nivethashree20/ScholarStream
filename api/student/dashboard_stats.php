<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    sendUnauthorized();
}

$student_id = $_SESSION['user_id'];

try {
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
        if ($row['submit_date'] && isset($chart_dates[$row['submit_date']])) {
            $chart_dates[$row['submit_date']] = (int)$row['count'];
        }
    }

    $chart_formatted = [];
    foreach ($chart_dates as $date => $count) {
        $chart_formatted[] = [
            'date' => $date,
            'label' => date('M j', strtotime($date)),
            'count' => $count
        ];
    }

    sendResponse(true, "Dashboard stats retrieved", [
        'stats' => $stats,
        'chart' => $chart_formatted
    ]);

} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}
?>
