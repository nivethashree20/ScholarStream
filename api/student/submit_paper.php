<?php
require_once '../../backend/config/db_connect.php';
require_once '../utils/response.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    sendUnauthorized();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendBadRequest("Method not allowed");
}

$student_id = $_SESSION['user_id'];
$academic_year = $_POST['academic_year'] ?? '';
$semester = $_POST['semester'] ?? '';
$organization = $_SESSION['organization'] ?? $_POST['organization'] ?? '';
$department = $_POST['department'] ?? '';
$research_area = $_POST['research_area'] ?? '';
$title = $_POST['title'] ?? '';
$guide_name = $_POST['guide_name'] ?? '';
$abstract = $_POST['abstract'] ?? '';
$parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
$co_author_emails = isset($_POST['co_authors']) ? json_decode($_POST['co_authors'], true) : [];

if (empty($title) || empty($abstract) || !isset($_FILES['research_paper'])) {
    sendResponse(false, "Missing required fields", null, 400);
}

// File Upload Handling
$target_dir = "../../backend/uploads/";
$file_extension = strtolower(pathinfo($_FILES["research_paper"]["name"], PATHINFO_EXTENSION));
$new_filename = time() . "_" . round(microtime(true)) . ".pdf";
$target_file = $target_dir . $new_filename;

if ($file_extension != "pdf") {
    sendResponse(false, "Only PDF files are allowed", null, 400);
}

if (move_uploaded_file($_FILES["research_paper"]["tmp_name"], $target_file)) {
    try {
        $pdo->beginTransaction();
        
        $version = 1;
        $root_parent_id = null;

        if ($parent_id) {
            $stmt = $pdo->prepare("SELECT version FROM research_papers WHERE id = ? OR parent_id = ? ORDER BY version DESC LIMIT 1");
            $stmt->execute([$parent_id, $parent_id]);
            $old_version = $stmt->fetchColumn();
            $version = $old_version + 1;

            $stmt = $pdo->prepare("UPDATE research_papers SET is_latest = FALSE WHERE id = ? OR parent_id = ?");
            $stmt->execute([$parent_id, $parent_id]);
            
            $stmt = $pdo->prepare("SELECT COALESCE(parent_id, id) FROM research_papers WHERE id = ?");
            $stmt->execute([$parent_id]);
            $root_parent_id = $stmt->fetchColumn();
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
        sendResponse(true, "Paper submitted successfully", ['id' => $new_paper_id]);
    } catch (\PDOException $e) {
        $pdo->rollBack();
        unlink($target_file); 
        sendResponse(false, "Submission failed: " . $e->getMessage(), null, 500);
    }
} else {
    sendResponse(false, "Failed to upload file", null, 500);
}
?>
