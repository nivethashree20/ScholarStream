<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $organization = $_POST['organization'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, organization) VALUES (?, ?, ?, 'student', ?)");
            if ($stmt->execute([$name, $email, $hashed_password, $organization])) {
                header("Location: login.php?role=student&registered=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Student Registration - ScholarStream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --accent: #bb86fc;
            --bg-dark: #121212;
            --card-bg: #1e1e2e;
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        body {
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 40px 20px;
        }

        .register-card {
            width: 100%;
            max-width: 500px;
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
        }

        .logo-box {
            background-color: var(--accent);
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: black;
            font-size: 1.8rem;
        }

        .logo-text {
            color: var(--accent);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        h2 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
            letter-spacing: -0.5px;
        }

        .form-label {
            display: block;
            text-align: left;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            margin-left: 0.25rem;
        }

        .form-control {
            background-color: #2a2a3c;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 16px;
            padding: 12px 18px;
            font-size: 1rem;
        }

        .form-control:focus {
            background-color: #2d2d42;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(187, 134, 252, 0.1);
            color: white;
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
            border-radius: 16px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            color: black;
            width: 100%;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            color: black;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo-box">
            <i class="bi bi-book-half"></i>
        </div>
        <div class="logo-text">ScholarStream</div>
        <h2>Student Registration</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger bg-danger bg-opacity-20 text-white border-0 rounded-3 mb-4 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@university.edu" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Organization / University</label>
                <input type="text" name="organization" class="form-control" placeholder="University Name" required>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="mt-2">
            <span class="text-white-50 small">Already have an account?</span>
            <a href="login.php?role=student" class="footer-link d-inline ms-1 fw-bold text-white">Sign In</a>
        </div>
    </div>
</body>
</html>
