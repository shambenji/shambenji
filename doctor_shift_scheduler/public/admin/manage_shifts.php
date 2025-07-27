<?php
session_start();

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Set success/error messages
$message = '';
$message_type = '';

// Handle shift assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shift_id'], $_POST['doctor_id'])) {
    try {
        // Validate data
        $shift_id = (int)$_POST['shift_id'];
        $doctor_id = $_POST['doctor_id'] === '' ? null : (int)$_POST['doctor_id'];

        if ($shift_id <= 0) {
            throw new Exception("Invalid shift ID");
        }

        if ($doctor_id !== null && $doctor_id <= 0) {
            throw new Exception("Invalid doctor ID");
        }

        // Begin transaction
        $pdo->beginTransaction();

        // 1. Update shift assignment
        $stmt = $pdo->prepare("UPDATE shifts SET doctor_id = ? WHERE id = ?");
        $stmt->execute([$doctor_id, $shift_id]);

        // 2. Send SMS notification if doctor assigned
        
if ($doctor_id !== null) {
    $stmt = $pdo->prepare("SELECT phone, name FROM doctors WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch();
    
    if ($doctor && !empty($doctor['phone'])) {
        $stmt = $pdo->prepare("SELECT shift_date, shift_type FROM shifts WHERE id = ?");
        $stmt->execute([$shift_id]);
        $shift = $stmt->fetch();
        
        $message = "Dear Dr. {$doctor['name']}, you've been assigned to {$shift['shift_type']} shift on " . 
                  date('l, jS F Y', strtotime($shift['shift_date']));
        
        sendSMS($doctor['phone'], $message);
    }
}

        $pdo->commit();
        
        $message = "Shift updated successfully!" . ($doctor_id ? " Notification sent to doctor." : "");
        $message_type = 'success';
        
        // Log the change
        $log = date('Y-m-d H:i:s') . " - Admin {$_SESSION['user_id']} updated shift $shift_id";
        file_put_contents(__DIR__ . '/../../logs/shift_changes.log', $log . PHP_EOL, FILE_APPEND);

    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Database error: " . $e->getMessage();
        $message_type = 'danger';
        error_log($e->getMessage());
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
        $message_type = 'danger';
        error_log($e->getMessage());
    }
}

// SMS sending function
function sendSMS($phone, $message) {
    // Replace this with your actual SMS API implementation
    // For testing purposes, return true
    return true;
}

// Get shifts data
try {
    $stmt = $pdo->query("
        SELECT s.id, s.shift_date, s.shift_type, 
               u.name AS doctor_name, u.id AS doctor_id
        FROM shifts s
        LEFT JOIN users u ON s.doctor_id = u.id
        WHERE s.shift_date >= CURDATE()
        ORDER BY s.shift_date, FIELD(s.shift_type, 'morning','evening','night')
    ");
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get doctors list
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role='doctor' ORDER BY name");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
    $message_type = 'danger';
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .shift-morning { background-color: #e6f7ff; }
        .shift-evening { background-color: #fff7e6; }
        .shift-night { background-color: #f0f0f0; }
        .unassigned { background-color: #ffebee; }
        body {
            background-color: #6a11cb;
            padding: 20px;
        }
        .btn-custom-secondary {
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            transition: all 0.3s;
        }
        .btn-custom-secondary:hover {
            background-color: #142eee;
            transform: translateY(-1px);
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Shift Management</h2>
            <a href="dashboard.php" class="btn-custom-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Shift Type</th>
                            <th>Doctor</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shifts as $shift): ?>
                        <tr class="shift-<?= htmlspecialchars($shift['shift_type']) ?> <?= empty($shift['doctor_name']) ? 'unassigned' : '' ?>">
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($shift['shift_date']))) ?></td>
                            <td><?= htmlspecialchars(ucfirst($shift['shift_type'])) ?></td>
                            <td><?= $shift['doctor_name'] ? htmlspecialchars($shift['doctor_name']) : 'Unassigned' ?></td>
                            <td>
                                <!-- <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="shift_id" value="<?= $shift['id'] ?>">
                                    <select name="doctor_id" class="form-select">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($doctors as $doc): ?>
                                            <option value="<?= $doc['id'] ?>" <?= $doc['id'] == $shift['doctor_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($doc['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                     <button type="submit" class="btn btn-primary">Save</button> 
                                </form> -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add AJAX form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                
                // Disable button during submission
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // Reload page to show updated data and messages
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitButton.disabled = false;
                    submitButton.textContent = 'Save';
                    alert('An error occurred. Please try again.');
                });
            });
        });
    });
    </script>
</body>
</html>