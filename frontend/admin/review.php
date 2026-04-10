<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT rp.*, u.name as student_name, u.email as student_email 
    FROM research_papers rp 
    JOIN users u ON rp.student_id = u.id 
    WHERE rp.id = ?");
$stmt->execute([$id]);
$paper = $stmt->fetch();

if (!$paper) {
    header("Location: dashboard.php");
    exit();
}

// Fetch co-authors
$stmt = $pdo->prepare("SELECT student_email FROM paper_coauthors WHERE paper_id = ?");
$stmt->execute([$id]);
$co_authors = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch version history
$root_id = $paper['parent_id'] ?: $paper['id'];
$stmt = $pdo->prepare("SELECT id, version, status, submitted_at FROM research_papers WHERE (id = ? OR parent_id = ?) ORDER BY version DESC");
$stmt->execute([$root_id, $root_id]);
$history = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    $feedback_message = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    $pdo->beginTransaction();
    try {
        // Update paper status
        $stmt = $pdo->prepare("UPDATE research_papers SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);

        // Save feedback if provided
        if (!empty($feedback_message)) {
            $stmt = $pdo->prepare("INSERT INTO paper_feedback (paper_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 1)");
            $stmt->execute([$id, $_SESSION['user_id'], $feedback_message]);
        }

        $pdo->commit();
        header("Location: dashboard.php?msg=Decision and feedback saved successfully");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to save: " . $e->getMessage();
    }
}

// Fetch feedback history
$stmt = $pdo->prepare("SELECT pf.*, u.name as user_name 
    FROM paper_feedback pf 
    JOIN users u ON pf.user_id = u.id 
    WHERE pf.paper_id = ? 
    ORDER BY pf.created_at ASC");
$stmt->execute([$id]);
$feedbacks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Paper - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.2">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold text-primary">Review Submission</h1>
                <p class="text-muted">Review research paper details and take action.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </header>

        <div class="row">
            <div class="col-lg-8">
                <div class="card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($paper['title']); ?></h3>
                            <p class="text-white opacity-50">Submitted: <?php echo date('M d, Y', strtotime($paper['submitted_at'])); ?></p>
                        </div>
                        <span class="badge bg-dark border border-secondary text-secondary px-3 py-2"><?php echo $paper['research_area']; ?></span>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-sm-6">
                            <label class="small text-white opacity-75 d-block mb-1">Student</label>
                            <span class="fw-bold text-white"><?php echo htmlspecialchars($paper['student_name']); ?></span>
                            <p class="small text-white opacity-50 mb-0"><?php echo htmlspecialchars($paper['organization']); ?></p>
                            <?php if (!empty($co_authors)): ?>
                                <p class="small text-white-50 mt-1 mb-0">Co-authors: <?php echo implode(', ', $co_authors); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-6">
                            <label class="small text-white opacity-75 d-block mb-1">Research Area</label>
                            <span class="fw-bold text-white"><?php echo htmlspecialchars($paper['research_area']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <label class="small text-white opacity-75 d-block mb-1">Department</label>
                            <span class="text-white"><?php echo htmlspecialchars($paper['department']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <label class="small text-white opacity-75 d-block mb-1">Academic Period</label>
                            <span class="text-white"><?php echo htmlspecialchars($paper['academic_year']); ?> (<?php echo htmlspecialchars($paper['semester']); ?>)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small text-white opacity-75 d-block mb-2">Abstract Content</label>
                        <div class="p-4 bg-dark rounded border border-secondary shadow-inner">
                            <p class="mb-0 text-white" style="white-space: pre-wrap; line-height: 1.6;"><?php echo htmlspecialchars($paper['abstract']); ?></p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 align-items-center">
                        <a href="../../backend/uploads/<?php echo $paper['file_path']; ?>" target="_blank" class="btn btn-secondary d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf me-2"></i> View PDF (v<?php echo $paper['version']; ?>)
                        </a>
                        <?php if (count($history) > 1): ?>
                            <div class="ms-auto">
                                <span class="small text-muted me-2">History:</span>
                                <?php foreach ($history as $h): ?>
                                    <a href="review.php?id=<?php echo $h['id']; ?>" class="badge border <?php echo $h['id'] == $id ? 'bg-primary border-primary' : 'bg-transparent border-secondary text-muted'; ?> text-decoration-none">
                                        v<?php echo $h['version']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-4">
                    <h5 class="mb-4">Decision</h5>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label small text-muted">Current Status</label>
                            <div class="fw-bold"><?php echo $paper['status']; ?></div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Feedback / Revision Notes</label>
                            <textarea name="feedback" class="form-control bg-dark text-white border-secondary" rows="4" placeholder="Explain your decision or list required revisions..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="status" value="Approved" class="btn btn-success py-2">
                                <i class="bi bi-check-circle me-2"></i> Approve Paper
                            </button>
                            <button type="submit" name="status" value="Revision Required" class="btn btn-warning py-2 text-dark">
                                <i class="bi bi-pencil-square me-2"></i> Request Revision
                            </button>
                            <button type="submit" name="status" value="Declined" class="btn btn-danger py-2">
                                <i class="bi bi-x-circle me-2"></i> Decline Paper
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Feedback History -->
                <div class="card p-4 mt-4">
                    <h5 class="mb-4">Communication Thread</h5>
                    <?php if (empty($feedbacks)): ?>
                        <p class="text-muted small">No feedback messages yet.</p>
                    <?php else: ?>
                        <div class="thread-container" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($feedbacks as $fb): ?>
                                <div class="mb-3 p-3 rounded <?php echo $fb['is_admin_reply'] ? 'bg-primary bg-opacity-10 border-primary' : 'bg-dark border-secondary'; ?> border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small fw-bold <?php echo $fb['is_admin_reply'] ? 'text-primary' : 'text-white'; ?>">
                                            <?php echo htmlspecialchars($fb['user_name']); ?> 
                                            <span class="text-muted">(<?php echo $fb['is_admin_reply'] ? 'Admin' : 'Student'; ?>)</span>
                                        </span>
                                        <span class="small text-muted"><?php echo date('M d, H:i', strtotime($fb['created_at'])); ?></span>
                                    </div>
                                    <p class="mb-0 small text-white"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
