<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

// Fetch paper details and ensure it belongs to the logged-in student
$stmt = $pdo->prepare("SELECT rp.*, u.name as student_name, u.email as student_email 
    FROM research_papers rp 
    JOIN users u ON rp.student_id = u.id 
    LEFT JOIN paper_coauthors pc ON rp.id = pc.paper_id
    WHERE rp.id = ? AND (rp.student_id = ? OR pc.student_email = (SELECT email FROM users WHERE id = ?))");
$stmt->execute([$id, $student_id, $student_id]);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $reply_message = trim($_POST['reply']);
    if (!empty($reply_message)) {
        $stmt = $pdo->prepare("INSERT INTO paper_feedback (paper_id, user_id, message, is_admin_reply) VALUES (?, ?, ?, 0)");
        $stmt->execute([$id, $student_id, $reply_message]);
        header("Location: view_paper.php?id=$id&msg=Reply sent");
        exit();
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
    <title>Activity Logger Details - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.6">
    <style>
        :root {
            --logger-bg: #1a1a2e;
            --logger-border: rgba(255, 255, 255, 0.15);
            --logger-label: rgba(255, 255, 255, 0.8);
            --logger-value: #ffffff;
            --logger-blue: #60a5fa; /* Lighter blue for better contrast */
        }

        body {
            background-color: #0f172a; /* Deep blue-gray background matching the image */
        }

        .main-content {
            padding: 2rem;
        }

        .logger-header {
            font-size: 0.9rem;
            color: var(--logger-label);
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logger-container {
            background-color: var(--logger-bg);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--logger-border);
        }

        .logger-row {
            display: flex;
            border-bottom: 1px solid var(--logger-border);
            min-height: 50px;
        }

        .logger-row:last-child {
            border-bottom: none;
        }

        .logger-label {
            width: 200px;
            padding: 12px 1.5rem;
            color: var(--logger-label);
            font-size: 0.85rem;
            border-right: 1px solid var(--logger-border);
            display: flex;
            align-items: center;
        }

        .logger-value {
            flex: 1;
            padding: 12px 1.5rem;
            color: var(--logger-value);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .status-approved { background-color: rgba(76, 175, 80, 0.15); color: #4caf50; }
        .status-declined { background-color: rgba(244, 67, 54, 0.15); color: #f44336; }
        .status-revision { background-color: rgba(255, 152, 0, 0.15); color: #ff9800; }

        .btn-view-pdf {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--logger-blue);
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-view-pdf:hover {
            opacity: 0.8;
            color: var(--logger-blue);
        }

        .text-blue { color: var(--logger-blue); }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="logger-header">
                <div>
                    Activity Logger Details: Student : <span class="text-white fw-medium"><?php echo htmlspecialchars($paper['student_name']); ?></span> - (<?php echo htmlspecialchars($paper['student_email']); ?>)
                </div>
                <div>
                    <a href="dashboard.php" class="text-muted text-decoration-none small">Close</a>
                </div>
            </div>

            <div class="logger-container">
                <div class="logger-row">
                    <div class="logger-label">ID</div>
                    <div class="logger-value"><?php echo 100000 + $paper['id']; ?></div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Student</div>
                    <div class="logger-value">
                        <span class="text-blue fw-medium"><?php echo htmlspecialchars($paper['student_name']); ?></span>
                        <span class="ms-1 opactiy-75">@<?php echo explode('@', $paper['student_email'])[0]; ?> (<?php echo htmlspecialchars($paper['department']); ?>)</span>
                    </div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Research Area</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['research_area']); ?></div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Research Title</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['title']); ?></div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Organization</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['organization']); ?></div>
                </div>

                <?php if (!empty($co_authors)): ?>
                <div class="logger-row">
                    <div class="logger-label">Co-authors</div>
                    <div class="logger-value text-white-50">
                        <?php echo implode(', ', array_map('htmlspecialchars', $co_authors)); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="logger-row">
                    <div class="logger-label">Guide Name</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['guide_name']); ?></div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Academic Year</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['academic_year']); ?></div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Semester</div>
                    <div class="logger-value"><?php echo htmlspecialchars($paper['semester']); ?></div>
                </div>

                <div class="logger-row" style="min-height: 100px; align-items: flex-start;">
                    <div class="logger-label" style="align-items: flex-start; padding-top: 15px;">Abstract</div>
                    <div class="logger-value" style="align-items: flex-start; padding-top: 15px; line-height: 1.6; color: #ffffff;">
                        <?php echo nl2br(htmlspecialchars($paper['abstract'])); ?>
                    </div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Status</div>
                    <div class="logger-value">
                        <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($paper['status'])); ?>">
                            <?php echo $paper['status']; ?>
                        </span>
                    </div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Research Paper</div>
                    <div class="logger-value d-flex justify-content-between align-items-center">
                        <a href="../../backend/uploads/<?php echo $paper['file_path']; ?>" target="_blank" class="btn-view-pdf">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            View PDF (v<?php echo $paper['version']; ?>)
                        </a>
                        <?php if (in_array($paper['status'], ['Declined', 'Revision Required']) && $paper['is_latest']): ?>
                            <a href="submit_paper.php?rev_id=<?php echo $paper['id']; ?>" class="btn btn-sm btn-warning text-dark fw-bold">
                                <i class="bi bi-pencil-square me-1"></i> Submit New Version
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="logger-row">
                    <div class="logger-label">Submitted Date</div>
                    <div class="logger-value"><?php echo date('d/m/Y H:i', strtotime($paper['submitted_at'])); ?></div>
                </div>

                <?php if (count($history) > 1): ?>
                <div class="logger-row bg-dark bg-opacity-25">
                    <div class="logger-label">Version History</div>
                    <div class="logger-value">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($history as $h): ?>
                                <a href="view_paper.php?id=<?php echo $h['id']; ?>" class="badge border <?php echo $h['id'] == $id ? 'bg-primary border-primary' : 'bg-transparent border-secondary text-muted'; ?> text-decoration-none">
                                    v<?php echo $h['version']; ?> (<?php echo $h['status']; ?>)
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Feedback Communication -->
            <div class="mt-5">
                <h5 class="text-white mb-4 d-flex align-items-center">
                    <i class="bi bi-chat-dots me-2 text-blue"></i> Feedback & Communication
                </h5>
                
                <div class="row">
                    <div class="col-lg-7">
                        <?php if (empty($feedbacks)): ?>
                            <div class="p-4 rounded border border-secondary bg-dark opacity-75 text-center">
                                <p class="text-muted mb-0 small">No feedback or messages yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="thread-container mb-4">
                                <?php foreach ($feedbacks as $fb): ?>
                                    <div class="mb-3 p-3 rounded <?php echo $fb['is_admin_reply'] ? 'bg-primary bg-opacity-10 border-primary' : 'bg-dark border-secondary'; ?> border">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small fw-bold <?php echo $fb['is_admin_reply'] ? 'text-primary' : 'text-blue'; ?>">
                                                <?php echo htmlspecialchars($fb['user_name']); ?> 
                                                <span class="text-muted small opacity-75">(<?php echo $fb['is_admin_reply'] ? 'Administrator' : 'You'; ?>)</span>
                                            </span>
                                            <span class="small text-muted"><?php echo date('M d, H:i', strtotime($fb['created_at'])); ?></span>
                                        </div>
                                        <p class="mb-0 small text-white"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-5">
                        <div class="p-4 rounded border border-secondary bg-dark">
                            <h6 class="text-white mb-3">Send a Message</h6>
                            <form method="POST">
                                <textarea name="reply" class="form-control bg-dark border-secondary text-white mb-3" rows="3" placeholder="Type your message or revision note..." required></textarea>
                                <button type="submit" class="btn btn-sm btn-primary w-100">Send Message</button>
                            </form>
                            <?php if ($paper['status'] === 'Revision Required'): ?>
                                <div class="mt-3 p-3 rounded bg-warning bg-opacity-10 border border-warning">
                                    <p class="small text-warning mb-0">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Admin has requested revisions. 
                                        Please update your paper once completed.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-muted small">BIT Information Portal v4.31.3 (ScholarStream Add-on) . © 2026 IT - Operations, Bannari Amman Institute of Technology</p>
            </div>
        </div>
    </div>
</body>
</html>
