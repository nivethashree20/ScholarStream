<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #8b5cf6;
            --bg-dark: #0a0a0a;
            --card-bg: #161616;
            --card-hover: #1c1c1c;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --text-secondary: rgba(255, 255, 255, 0.5);
        }

        body {
            background-color: var(--bg-dark);
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .main-card {
            width: 100%;
            max-width: 600px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
        }

        .logo-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2.5rem;
        }

        .logo-box {
            background-color: var(--primary);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -1px;
        }

        .sub-header {
            color: var(--text-secondary);
            font-size: 1.1rem;
            line-height: 1.5;
            max-width: 450px;
            margin: 0 auto 3rem;
        }

        .role-selectors {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .role-card {
            background-color: #1f1f1f;
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
        }

        .role-card:hover {
            background-color: var(--card-hover);
            border-color: rgba(139, 92, 246, 0.3);
            transform: translateY(-2px);
            color: white;
        }

        .role-icon-box {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 1.6rem;
            color: var(--primary);
            transition: all 0.2s;
        }

        .role-card:hover .role-icon-box {
            background-color: rgba(139, 92, 246, 0.1);
        }

        .role-info {
            flex-grow: 1;
        }

        .role-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .role-info p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .arrow-box {
            color: var(--text-secondary);
            font-size: 1.2rem;
            opacity: 0.5;
            transition: all 0.2s;
        }

        .role-card:hover .arrow-box {
            transform: translateX(5px);
            opacity: 1;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="main-card">
        <div class="logo-header">
            <div class="logo-box">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="logo-text">ScholarStream</div>
        </div>

        <h1>Welcome to ScholarStream</h1>
        <p class="sub-header">The platform for academic research submission and review. Please select your role to continue.</p>

        <div class="role-selectors">
            <a href="login.php?role=student" class="role-card">
                <div class="role-icon-box">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div class="role-info">
                    <h3>Student Portal</h3>
                    <p>Submit and track your research papers.</p>
                </div>
                <div class="arrow-box">
                    <i class="bi bi-arrow-right"></i>
                </div>
            </a>

            <a href="login.php?role=admin" class="role-card">
                <div class="role-icon-box">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div class="role-info">
                    <h3>Admin Portal</h3>
                    <p>Review and manage submissions.</p>
                </div>
                <div class="arrow-box">
                    <i class="bi bi-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>
</body>
</html>
