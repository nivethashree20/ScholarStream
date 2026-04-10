<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';

// Check if this is a revision of an existing paper
$rev_id = isset($_GET['rev_id']) ? (int)$_GET['rev_id'] : 0;
$rev_paper = null;
if ($rev_id) {
    $stmt = $pdo->prepare("SELECT * FROM research_papers WHERE id = ? AND student_id = ?");
    $stmt->execute([$rev_id, $_SESSION['user_id']]);
    $rev_paper = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $organization = $_POST['organization'];
    $department = $_POST['department'];
    $research_area = $_POST['research_area'];
    $title = $_POST['title'];
    $guide_name = $_POST['guide_name'];
    $abstract = $_POST['abstract'];
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $co_author_emails = isset($_POST['co_authors']) ? array_filter(array_map('trim', $_POST['co_authors'])) : [];

    // Registration Check for Co-authors
    if (!empty($co_author_emails)) {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ? AND role = 'student'");
        foreach ($co_author_emails as $email) {
            $stmt->execute([$email]);
            if (!$stmt->fetch()) {
                $error = "The email '<b>$email</b>' is not registered as a student in ScholarStream. Please ask them to register first.";
                break;
            }
        }
    }

    if (empty($error)) {
        // Server-side word count validation
    $word_count = count(preg_split('/\s+/', trim($abstract)));
    
    // File Upload Handling
    $target_dir = "../../backend/uploads/";
    $file_extension = strtolower(pathinfo($_FILES["research_paper"]["name"], PATHINFO_EXTENSION));
    $new_filename = time() . "_" . round(microtime(true)) . ".pdf";
    $target_file = $target_dir . $new_filename;

    if ($word_count > 500) {
        $error = "Abstract exceeds the 500 words limit.";
    } elseif ($file_extension != "pdf") {
        $error = "Only PDF files are allowed.";
    } elseif ($_FILES["research_paper"]["size"] > 10000000) { // 10MB limit
        $error = "File is too large (max 10MB).";
    } else {
        if (move_uploaded_file($_FILES["research_paper"]["tmp_name"], $target_file)) {
            try {
                $pdo->beginTransaction();
                
                $version = 1;
                if ($parent_id) {
                    // Fetch current version and update old latest
                    $stmt = $pdo->prepare("SELECT version FROM research_papers WHERE id = ? OR parent_id = ? ORDER BY version DESC LIMIT 1");
                    $stmt->execute([$parent_id, $parent_id]);
                    $old_version = $stmt->fetchColumn();
                    $version = $old_version + 1;

                    $stmt = $pdo->prepare("UPDATE research_papers SET is_latest = FALSE WHERE id = ? OR parent_id = ?");
                    $stmt->execute([$parent_id, $parent_id]);
                    
                    // The root parent_id remains the same for all versions
                    $stmt = $pdo->prepare("SELECT COALESCE(parent_id, id) FROM research_papers WHERE id = ?");
                    $stmt->execute([$parent_id]);
                    $root_parent_id = $stmt->fetchColumn();
                } else {
                    $root_parent_id = null;
                }

                $stmt = $pdo->prepare("INSERT INTO research_papers (student_id, academic_year, semester, organization, department, research_area, title, guide_name, abstract, file_path, version, parent_id, is_latest) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)");
                $stmt->execute([$student_id, $academic_year, $semester, $organization, $department, $research_area, $title, $guide_name, $abstract, $new_filename, $version, $root_parent_id]);
                $new_paper_id = $pdo->lastInsertId();

                // Handle Co-authors
                if (!empty($co_author_emails)) {
                    $stmt = $pdo->prepare("INSERT INTO paper_coauthors (paper_id, student_email) VALUES (?, ?)");
                    foreach ($co_author_emails as $email) {
                        $stmt->execute([$new_paper_id, $email]);
                    }
                }

                $pdo->commit();
                $success = "Research paper " . ($parent_id ? "revision (v$version)" : "submitted") . " successfully! Redirecting...";
                header("Refresh: 2; URL=dashboard.php");
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $error = "Data insertion failed: " . $e->getMessage();
                unlink($target_file); 
            }
        } else {
            $error = "Failed to upload file.";
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Paper - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=1.2">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="mb-5">
            <h1 class="fw-bold text-white" style="font-size: 2.5rem;">Research Paper Registration</h1>
            <p class="text-white opacity-75 fs-5">Fill out the details below to submit your research for approval.</p>
        </header>

        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card p-5" style="background-color: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <h4 class="mb-4 fw-bold text-white">Paper Details</h4>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 bg-success bg-opacity-20 text-white py-3"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 bg-danger bg-opacity-20 text-white py-3"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="registrationForm">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white opacity-90">Student Name</label>
                                <input type="text" class="form-control text-white" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white opacity-90">Organization</label>
                                <input type="text" name="organization" class="form-control text-white" value="<?php echo htmlspecialchars($_SESSION['organization'] ?? ''); ?>" readonly style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-2 text-white">Academic Year</label>
                                <select name="academic_year" id="academicYear" class="form-select text-white" required style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                                    <option value="" disabled selected>Select an academic year</option>
                                    <option value="2022-2026">2022-2026</option>
                                    <option value="2023-2027">2023-2027</option>
                                    <option value="2024-2028">2024-2028</option>
                                    <option value="2025-2029">2025-2029</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold mb-2 text-white">Semester</label>
                                <select name="semester" id="semester" class="form-select text-white" required style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                                    <option value="" disabled selected>Select an academic year first</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white">Department</label>
                                <select name="department" class="form-select text-white" required style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                                    <option value="" disabled selected>Select a department</option>
                                    <option>Computer Science Engineering</option>
                                    <option>Information Science & Engineering</option>
                                    <option>Artificial Intelligence & Data Science</option>
                                    <option>Information Technology</option>
                                    <option>Artificial Intelligence & Machine Learning</option>
                                    <option>Computer Technology</option>
                                    <option>Electronics & Communication Engineering</option>
                                    <option>Electrical & Electronics Engineering</option>
                                    <option>Biotechnology</option>
                                    <option>Biomedical Engineering</option>
                                    <option>Civil Engineering</option>
                                    <option>Mechanical Engineering</option>
                                    <option>Chemical Engineering</option>
                                    <option>Aerospace Engineering</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white">Research Area</label>
                                <input type="text" name="research_area" class="form-control text-white" placeholder="Enter research area (e.g. Deep Learning)" required value="<?php echo $rev_paper ? htmlspecialchars($rev_paper['research_area']) : ''; ?>" style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white">Research Title</label>
                                <input type="text" name="title" class="form-control text-white" placeholder="Enter your research title" required value="<?php echo $rev_paper ? htmlspecialchars($rev_paper['title']) : ''; ?>" style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white">Number of Co-authors</label>
                                <select id="coAuthorCount" class="form-select text-white mb-3" style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                                    <option value="0">No Co-authors</option>
                                    <option value="1">1 Co-author</option>
                                    <option value="2">2 Co-authors</option>
                                    <option value="3">3 Co-authors</option>
                                    <option value="4">4 Co-authors</option>
                                </select>
                                <div id="coAuthorInputs" class="row g-3">
                                    <!-- Dynamic inputs will appear here -->
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2 text-white">Research Guide</label>
                                <input type="text" name="guide_name" class="form-control text-white" placeholder="e.g., Dr. Jane Doe" required value="<?php echo $rev_paper ? htmlspecialchars($rev_paper['guide_name']) : ''; ?>" style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                            </div>
                            
                            <?php if ($rev_id): ?>
                                <input type="hidden" name="parent_id" value="<?php echo $rev_id; ?>">
                            <?php endif; ?>

                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0 text-white">Abstract</label>
                                    <span id="wordCount" class="small text-white opacity-50">0 / 500 words</span>
                                </div>
                                <textarea name="abstract" id="abstractText" class="form-control text-white" rows="6" placeholder="Provide a concise summary of your research..." required style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);"><?php echo isset($_POST['abstract']) ? htmlspecialchars($_POST['abstract']) : ''; ?></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold mb-2">Upload Research Paper</label>
                                <input type="file" name="research_paper" class="form-control" accept=".pdf" required>
                                <p class="small text-muted mt-2">Upload your research paper in PDF format (Max 10MB).</p>
                            </div>

                            <div class="col-md-12 mt-4 text-center">
                                <button type="submit" class="btn btn-primary px-5 py-3 fs-5">Submit for Review</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dynamic Semesters Logic
        const yearSelect = document.getElementById('academicYear');
        const semesterSelect = document.getElementById('semester');

        const semesterData = {
            '2022-2026': ['Semester 7', 'Semester 8'],
            '2023-2027': ['Semester 5', 'Semester 6'],
            '2024-2028': ['Semester 3', 'Semester 4'],
            '2025-2029': ['Semester 1', 'Semester 2']
        };

        yearSelect.addEventListener('change', function() {
            const selectedYear = this.value;
            const semesters = semesterData[selectedYear] || [];
            
            // Clear current options
            semesterSelect.innerHTML = '<option value="" disabled selected>Select a semester</option>';
            
            // Add new options
            semesters.forEach(sem => {
                const option = document.createElement('option');
                option.value = sem;
                option.textContent = sem;
                semesterSelect.appendChild(option);
            });
        });

        // Co-authors Dynamic Logic
        const coAuthorCount = document.getElementById('coAuthorCount');
        const coAuthorInputs = document.getElementById('coAuthorInputs');

        coAuthorCount.addEventListener('change', function() {
            const count = parseInt(this.value);
            coAuthorInputs.innerHTML = '';
            
            for (let i = 1; i <= count; i++) {
                const col = document.createElement('div');
                col.className = 'col-md-6';
                col.innerHTML = `
                    <label class="form-label small text-white opacity-75">Co-author ${i} Email</label>
                    <input type="email" name="co_authors[]" class="form-control text-white" 
                           placeholder="Enter registered email" required 
                           style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                `;
                coAuthorInputs.appendChild(col);
            }
        });

        const abstractArea = document.getElementById('abstractText');
        const countDisplay = document.getElementById('wordCount');
        
        abstractArea.addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/).filter(word => word.length > 0);
            const count = words.length;
            countDisplay.textContent = `${count.toLocaleString()} / 500 words`;
            
            if (count > 500) {
                countDisplay.classList.remove('text-muted');
                countDisplay.classList.add('text-danger');
                this.setCustomValidity('Abstract exceeds 500 words limit.');
            } else {
                countDisplay.classList.remove('text-danger');
                countDisplay.classList.add('text-muted');
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
