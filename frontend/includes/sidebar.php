<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column">
    <div class="p-4 d-flex align-items-center mb-4">
        <div class="logo-box me-2" style="background-color: var(--primary); width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
        </div>
        <h5 class="text-primary mb-0 fw-bold">ScholarStream</h5>
    </div>
    
    <nav class="nav flex-column flex-grow-1">
        <?php if ($_SESSION['role'] === 'student'): ?>
            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-file-earmark-text me-2"></i> Research Paper Details
            </a>
            <a class="nav-link <?php echo $current_page == 'submit_paper.php' ? 'active' : ''; ?>" href="submit_paper.php">
                <i class="bi bi-plus-circle me-2"></i> Research paper registration
            </a>
        <?php else: ?>
            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i> Admin Dashboard
            </a>
            <a class="nav-link <?php echo ($current_page == 'submissions.php' || $current_page == 'review.php') ? 'active' : ''; ?>" href="submissions.php">
                <i class="bi bi-card-list me-2"></i> Review Submissions
            </a>
        <?php endif; ?>
    </nav>

    <div class="mt-auto p-3">
        <div class="card p-3" style="background-color: rgba(255,255,255,0.03);">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-dark fw-bold me-2" style="width: 32px; height: 32px;">
                    <?php echo substr($_SESSION['name'], 0, 1); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="mb-0 small fw-bold text-truncate"><?php echo $_SESSION['name']; ?></p>
                    <a href="../auth/logout.php" class="text-muted small text-decoration-none">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
