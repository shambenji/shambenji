<?php
session_start(); // Hakikisha session imeanzishwa

// Hakiki kama mtumiaji ameingia na ni daktari
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    // Kama hakuingiwa, rudisha kwenye login na ujumbe
    $_SESSION['login_message'] = 'Tafadhali ingia kwanza';
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Hakiki nenosiri
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Tafadhali jaza sehemu zote';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Nenosiri jipya halifanani';
    } else {
        try {
            // Pata nenosiri la sasa la daktari
            $stmt = $pdo->prepare("SELECT password FROM doctors WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($doctor && password_verify($current_password, $doctor['password'])) {
                // Nenosiri sahihi, sasisha kwenye database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE doctors SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $_SESSION['message'] = 'Nenosiri limebadilishwa kikamilifu!';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Nenosiri la sasa si sahihi';
            }
        } catch (PDOException $e) {
            $error = "Hitilafu ya database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badilisha Nenosiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11ca, #0d47a1);
            min-height: 100vh;
            padding-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-bottom: none;
            background-color: rgba(255, 255, 255, 0.03);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-key"></i> Badilisha Nenosiri</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Nenosiri la Sasa</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nenosiri Jipya</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Angalau herufi 6</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Thibitisha Nenosiri</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Wasilisha
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Rudi kwenye Dashibodi
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>