<?php
// public/register.php

require_once __DIR__ . '/../controllers/auth.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;

    $response = register($name, $email, $password, $phone);
    
    if (strpos($response, 'kikamilifu') !== false) {
        $success = $response;
        // Clear form on success
        $_POST = [];
    } else {
        $error = $response;
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sajili - Doctor Shift Scheduler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .phone-input-group .input-group-text {
            background-color: #f8f9fa;
        }
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="form-container bg-white">
            <h2 class="text-center mb-4">Sajili Akaunti Mpya</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registrationForm">
                <div class="mb-3">
                    <label for="name" class="form-label">Jina Kamili:</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Barua Pepe:</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Nenosiri:</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" 
                               minlength="8" required>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <small class="text-muted">Angalau herufi 8</small>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Namba ya Simu (Si lazima):</label>
                    <div class="input-group phone-input-group">
                        <span class="input-group-text">+255</span>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               pattern="[0-9]{9}" maxlength="9"
                               placeholder="712345678"
                               value="<?= htmlspecialchars(isset($_POST['phone']) ? preg_replace('/^\+255/', '', $_POST['phone']) : '') ?>">
                    </div>
                    <small class="text-muted">Weka bila 0 ya kwanza (mfano: 712345678)</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Sajili</button>
                </div>
                
                <p class="mt-3 text-center">
                    Una akaunti tayari? <a href="login.php" class="text-decoration-none">Ingia hapa</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    
    <!-- JavaScript Validation -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const phoneValue = phoneInput.value.replace(/[^0-9]/g, '');
            
            // Validate phone if provided
            if (phoneValue && phoneValue.length !== 9) {
                alert('Tafadhali weka namba sahihi ya simu (tarakimu 9 bila 0 ya kwanza)');
                e.preventDefault();
                return false;
            }
            
            // Validate password length
            if (document.getElementById('password').value.length < 8) {
                alert('Nenosiri lazima liwe na herufi 8 au zaidi');
                e.preventDefault();
                return false;
            }
                           
            // Format phone number before submission if provided
            if (phoneValue) {
                phoneInput.value = '+255' + phoneValue;
            }
        });

        // Auto-format phone input
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
        });
    </script>
</body>
</html>