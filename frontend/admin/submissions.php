<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all papers with student info
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$filter_status = isset($_GET['status']) && $_GET['status'] != '' ? $_GET['status'] : '%';

$stmt = $pdo->prepare("SELECT rp.*, u.name as student_name 
    FROM research_papers rp 
    JOIN users u ON rp.student_id = u.id 
    WHERE (rp.title LIKE ? OR u.name LIKE ?) 
    AND rp.status LIKE ?
    ORDER BY rp.submitted_at DESC");
$stmt->execute([$search, $search, $filter_status]);
$papers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.2">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="mb-5">
            <h1 class="fw-bold text-primary">Review Submissions</h1>
            <p class="text-muted">Manage and review all research papers submitted by students.</p>
        </header>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form action="" method="GET" class="d-flex gap-3" style="flex-grow: 1; max-width: 600px;">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-transparent border-end-0" style="background: rgba(255,255,255,0.05) !important; border-color: rgba(255,255,255,0.1);"><i class="bi bi-search text-white opacity-75"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 text-white" placeholder="Search by title, student..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                    </div>
                    <select name="status" class="form-select w-auto text-white shadow-sm" onchange="this.form.submit()" style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                        <option value="" class="bg-dark">All Status</option>
                        <option value="Pending" class="bg-dark" <?php echo isset($_GET['status']) && $_GET['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" class="bg-dark" <?php echo isset($_GET['status']) && $_GET['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Revision Required" class="bg-dark" <?php echo isset($_GET['status']) && $_GET['status'] == 'Revision Required' ? 'selected' : ''; ?>>Revision</option>
                        <option value="Declined" class="bg-dark" <?php echo isset($_GET['status']) && $_GET['status'] == 'Declined' ? 'selected' : ''; ?>>Declined</option>
                    </select>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-uppercase" style="background: rgba(255,255,255,0.02); color: #ffffff;">
                        <tr>
                            <th class="border-0 ps-3 py-3">Student</th>
                            <th class="border-0 py-3">Organization</th>
                            <th class="border-0 py-3">Title</th>
                            <th class="border-0 py-3">Area</th>
                            <th class="border-0 py-3">Status</th>
                            <th class="border-0 text-end py-3 pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($papers)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No papers found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($papers as $paper): ?>
                                <tr>
                                    <td class="ps-3 border-bottom-0"><span style="color: #ffffff;"><?php echo htmlspecialchars((string)($paper['student_name'] ?? '')); ?></span></td>
                                    <td class="border-bottom-0"><span style="color: #ffffff;"><?php echo htmlspecialchars((string)($paper['organization'] ?? '')); ?></span></td>
                                    <td class="border-bottom-0"><span style="color: #ffffff;"><?php echo htmlspecialchars((string)($paper['title'] ?? '')); ?></span></td>
                                    <td class="border-bottom-0"><span style="color: #ffffff;"><?php echo htmlspecialchars((string)($paper['research_area'] ?? '')); ?></span></td>
                                    <td class="border-bottom-0"><span style="color: #ffffff;"><?php echo htmlspecialchars((string)($paper['status'] ?? '')); ?></span></td>
                                    <td class="text-end border-bottom-0">
                                        <a href="review.php?id=<?php echo $paper['id']; ?>" class="btn btn-sm btn-outline-light" style="font-size: 0.75rem;">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
