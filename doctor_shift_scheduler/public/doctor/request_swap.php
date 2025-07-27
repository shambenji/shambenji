<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$message = '';
$availableDoctors = [];
$myShifts = [];

try {
    // Get available doctors (excluding current user)
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM doctors 
        WHERE id != ? 
        AND active = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $availableDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get current doctor's upcoming shifts
    $stmt = $pdo->prepare("
        SELECT id, shift_date, shift_type 
        FROM shifts 
        WHERE doctor_id = ? 
        AND shift_date >= CURDATE()
        ORDER BY shift_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $myShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $targetDoctorId = filter_input(INPUT_POST, 'target_doctor_id', FILTER_VALIDATE_INT);
        $shiftId = filter_input(INPUT_POST, 'shift_id', FILTER_VALIDATE_INT);

        if ($targetDoctorId && $shiftId) {
            // Create new swap request
            $stmt = $pdo->prepare("
                INSERT INTO shift_swaps 
                (doctor_id, requested_shift_id, target_doctor_id, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $shiftId,
                $targetDoctorId
            ]);
            
            $message = "Swap request submitted successfully!";
        } else {
            $message = "Please select both a doctor and a shift";
        }
    }
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Shift Swap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body class="container py-4">
    <h2>Request Shift Swap</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="target_doctor_id" class="form-label">Swap With Doctor:</label>
            <select class="form-select" id="target_doctor_id" name="target_doctor_id" required>
                <option value="">Select a doctor</option>
                <?php foreach ($availableDoctors as $doctor): ?>
                    <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="shift_id" class="form-label">Select Your Shift to Swap:</label>
            <select class="form-select" id="shift_id" name="shift_id" required>
                <option value="">Select a shift</option>
                <?php foreach ($myShifts as $shift): ?>
                    <option value="<?= $shift['id'] ?>">
                        <?= htmlspecialchars($shift['shift_date']) ?> - 
                        <?= htmlspecialchars(ucfirst($shift['shift_type'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</body>
</html>