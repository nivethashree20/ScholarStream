<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT rp.*, u.name as student_name 
    FROM research_papers rp 
    JOIN users u ON rp.student_id = u.id 
    WHERE rp.id = ? AND rp.status = 'Approved'");
$stmt->execute([$id]);
$paper = $stmt->fetch();

if (!$paper) {
    die("Certificate not available for this submission.");
}

// Fetch co-authors
$stmt = $pdo->prepare("SELECT student_email FROM paper_coauthors WHERE paper_id = ?");
$stmt->execute([$id]);
$co_authors = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate - <?php echo htmlspecialchars($paper['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Inter:wght@400;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            font-family: 'Inter', sans-serif;
        }
        .certificate-container {
            width: 800px;
            height: 600px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 15px double #1a1a2e;
            text-align: center;
        }
        .inner-border {
            border: 2px solid #1a1a2e;
            height: 100%;
            padding: 30px;
            box-sizing: border-box;
        }
        .logo {
            font-family: 'Cinzel', serif;
            font-size: 28px;
            color: #1a1a2e;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }
        .cert-title {
            font-family: 'Cinzel', serif;
            font-size: 42px;
            color: #1a1a2e;
            margin: 30px 0;
            text-transform: uppercase;
        }
        .presented-to {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .description {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .paper-title {
            font-weight: 600;
            font-style: italic;
        }
        .footer-info {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-around;
        }
        .signature {
            border-top: 1px solid #333;
            width: 200px;
            padding-top: 10px;
            font-size: 14px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            font-size: 150px;
            font-family: 'Cinzel', serif;
            pointer-events: none;
            white-space: nowrap;
        }
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1a1a2e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        @media print {
            .btn-print { display: none; }
            body { background: white; }
            .certificate-container { 
                box-shadow: none; 
                margin: 0; 
                width: 100%;
                height: 100%;
                border-width: 10px;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Download / Print Certificate</button>

    <div class="certificate-container">
        <div class="watermark">SCHOLAR</div>
        <div class="inner-border">
            <div class="logo">SCHOLARSTREAM</div>
            <div class="cert-title">Certificate of Publication</div>
            
            <p class="presented-to">This is to certify that</p>
            <div class="student-name"><?php echo htmlspecialchars($paper['student_name']); ?></div>
            
            <p class="description">
                has successfully submitted and published the research paper titled: <br>
                <span class="paper-title">"<?php echo htmlspecialchars($paper['title']); ?>"</span> <br>
                in the area of <strong><?php echo htmlspecialchars($paper['research_area']); ?></strong>.
            </p>
            
            <p class="description" style="font-size: 14px; opacity: 0.8;">
                Institution: <?php echo htmlspecialchars($paper['organization']); ?> <br>
                Published Date: <?php echo date('F d, Y', strtotime($paper['submitted_at'])); ?> <br>
                Verification ID: # <?php echo 100000 + $paper['id']; ?>
            </p>

            <div class="footer-info">
                <div class="signature">
                    <strong>Admin Controller</strong> <br>
                    ScholarStream Portal
                </div>
                <div class="signature">
                    <strong>Research Guide</strong> <br>
                    <?php echo htmlspecialchars($paper['guide_name']); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
