<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Initialize message variable
$message = '';

// Handle swap approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['swap_id'])) {
    $swap_id = filter_input(INPUT_POST, 'swap_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    try {
        if ($action === 'approve') {
            // Get swap details first
            $stmt = $pdo->prepare("
                SELECT ss.*, s1.doctor_id AS current_doctor_id, s2.doctor_id AS requesting_doctor_id
                FROM shift_swaps ss
                JOIN shifts s1 ON ss.requested_shift_id = s1.id
                JOIN shifts s2 ON (
                    SELECT id FROM shifts 
                    WHERE doctor_id = ss.target_doctor_id 
                    AND shift_date = s1.shift_date 
                    AND shift_type = s1.shift_type
                    LIMIT 1
                )
                WHERE ss.id = ?
            ");
            $stmt->execute([$swap_id]);
            $swap = $stmt->fetch();

            if ($swap) {
                // Begin transaction
                $pdo->beginTransaction();

                // Update the shifts
                $stmt = $pdo->prepare("UPDATE shifts SET doctor_id = ? WHERE id = ?");
                $stmt->execute([$swap['requesting_doctor_id'], $swap['requested_shift_id']]);
                
                // Find the target shift to swap
                $stmt = $pdo->prepare("
                    UPDATE shifts SET doctor_id = ? 
                    WHERE doctor_id = ? 
                    AND shift_date = (SELECT shift_date FROM shifts WHERE id = ?)
                    AND shift_type = (SELECT shift_type FROM shifts WHERE id = ?)
                ");
                $stmt->execute([
                    $swap['current_doctor_id'],
                    $swap['target_doctor_id'],
                    $swap['requested_shift_id'],
                    $swap['requested_shift_id']
                ]);

                // Mark swap as approved
                $stmt = $pdo->prepare("UPDATE shift_swaps SET status = 'approved' WHERE id = ?");
                $stmt->execute([$swap_id]);

                $pdo->commit();
                $message = "Swap approved successfully!";
            }
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE shift_swaps SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$swap_id]);
            $message = "Swap rejected successfully!";
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error: " . $e->getMessage();
    }
}

try {
    // Fetch pending swap requests with doctor and shift information
    $stmt = $pdo->query("
        SELECT 
            ss.id AS swap_id,
            ss.status,
            ss.created_at,
            ss.requested_shift_id,
            ss.target_doctor_id,
            requesting_shift.shift_date,
            requesting_shift.shift_type,
            requesting_doctor.name AS requesting_doctor_name,
            target_doctor.name AS target_doctor_name,
            target_shift.shift_date AS target_shift_date,
            target_shift.shift_type AS target_shift_type
        FROM 
            shift_swaps ss
        JOIN 
            shifts requesting_shift ON ss.requested_shift_id = requesting_shift.id
        JOIN 
            doctors requesting_doctor ON requesting_shift.doctor_id = requesting_doctor.id
        JOIN 
            doctors target_doctor ON ss.target_doctor_id = target_doctor.id
        JOIN 
            shifts target_shift ON (
                target_shift.doctor_id = ss.target_doctor_id 
                AND target_shift.shift_date = requesting_shift.shift_date
                AND target_shift.shift_type = requesting_shift.shift_type
            )
        WHERE 
            ss.status = 'pending'
        ORDER BY 
            ss.created_at DESC
    ");
    $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shift Swaps - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
        .swap-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .swap-approved {
            border-left-color: #198754;
        }
        .swap-rejected {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Shift Swaps</h2>
        <a href="dashboard.php" class="btn-custom-secondary">Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= strpos($message, 'Error') !== false ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($swaps)): ?>
        <div class="alert alert-info">No pending shift swap requests found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($swaps as $swap): ?>
            <div class="col-md-6">
                <div class="card swap-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Swap Request #<?= htmlspecialchars($swap['swap_id']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Current Shift</h6>
                                <p>
                                    <strong>Doctor:</strong> <?= htmlspecialchars($swap['requesting_doctor_name']) ?><br>
                                    <strong>Date:</strong> <?= htmlspecialchars(date('D, M j, Y', strtotime($swap['shift_date']))) ?><br>
                                    <strong>Type:</strong> <?= htmlspecialchars(ucfirst($swap['shift_type'])) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Requested Swap With</h6>
                                <p>
                                    <strong>Doctor:</strong> <?= htmlspecialchars($swap['target_doctor_name']) ?><br>
                                    <strong>Date:</strong> <?= htmlspecialchars(date('D, M j, Y', strtotime($swap['target_shift_date']))) ?><br>
                                    <strong>Type:</strong> <?= htmlspecialchars(ucfirst($swap['target_shift_type'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                Requested on: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($swap['created_at']))) ?>
                            </small>
                            <div>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="swap_id" value="<?= $swap['swap_id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-success me-2">Approve</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="swap_id" value="<?= $swap['swap_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>