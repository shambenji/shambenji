
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (login($email, $password)) {
        // Redirect based on role
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: doctor/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #fff;
            padding: 3.5rem 4rem;
            border-radius: 19px;
            box-shadow: 0 8px 32px rgba(44, 62, 80, 0.15);
            width: 100%;
            max-width: 470px;
            margin: 2rem auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .btn-primary {
            background-color: #1e88e5;
            border-color: #1e88e5;
        }
    </style>

</head>
<body class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 login-container">
            <h2 class="mb-4 text-center">Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <!-- <p class="mt-3 text-center"> Don't have an account? <a href="register.php">Register here</a> -->
                </p>
            </form>
        </div>
    </div>
</body>
</html>