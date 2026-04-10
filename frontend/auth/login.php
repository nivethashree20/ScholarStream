<?php
require_once '../../backend/config/db_connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$role_param = isset($_GET['role']) ? $_GET['role'] : 'student';
$role_title = $role_param === 'admin' ? 'Administrator' : 'Student';
$accent_color = $role_param === 'admin' ? '#818cf8' : '#bb86fc';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $requested_role = $_POST['role'];

    // For admin role, we strictly enforce the role in the query
    $query = "SELECT * FROM users WHERE email = ?";
    if ($requested_role === 'admin') {
        $query .= " AND role = 'admin'";
    } else {
        $query .= " AND role = 'student'";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['organization'] = $user['organization'] ?? '';
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Invalid " . ($requested_role === 'admin' ? 'administrator' : 'student') . " credentials.";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $role_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --accent: <?php echo $accent_color; ?>;
            --bg-dark: #121212;
            --card-bg: #1e1e2e;
            --input-bg: rgba(255, 255, 255, 0.05);
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
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
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
            color: <?php echo $role_param === 'admin' ? 'white' : 'black'; ?>;
            font-size: 1.8rem;
        }

        .logo-text {
            color: var(--accent);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
        }

        h2 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
            letter-spacing: -0.5px;
        }

        .alert {
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            border: none;
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
            padding: 14px 20px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: #2d2d42;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.1);
            color: white;
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
            border-radius: 16px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            color: <?php echo $role_param === 'admin' ? 'white' : 'black'; ?>;
            width: 100%;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            color: <?php echo $role_param === 'admin' ? 'white' : 'black'; ?>;
        }

        .footer-link {
            display: block;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-link {
            margin-top: 1.5rem;
            opacity: 0.6;
        }

        .footer-link:hover {
            color: #ffffff;
        }

        .register-text {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-box">
            <i class="bi bi-book-half"></i>
        </div>
        <div class="logo-text">ScholarStream</div>
        <h2><?php echo $role_title; ?> Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger bg-danger bg-opacity-20 text-white"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success bg-success bg-opacity-20 text-white">Registration successful! Please login.</div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="role" value="<?php echo $role_param; ?>">
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <?php if ($role_param === 'student'): ?>
            <div class="register-text">
                <span class="text-white-50 small">Don't have an account?</span>
                <a href="register.php" class="footer-link d-inline ms-1 fw-bold text-white">Sign Up</a>
            </div>
        <?php endif; ?>

        <a href="index.php" class="footer-link back-link">← Back to Portal Selection</a>
    </div>
</body>
</html>
