<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

// Initialize message variable
$message = '';

try {
    // Fetch pending shift requests
    $stmt = $pdo->query("
        SELECT 
            sr.id,
            sr.doctor_id,
            sr.shift_date,
            sr.shift_type,
            sr.status,
            sr.created_at,
            d.name AS doctor_name
        FROM 
            swap_requests sr
        JOIN 
            doctors d ON sr.doctor_id = d.id
        WHERE 
            sr.status = 'pending'
        ORDER BY 
            sr.created_at DESC
    ");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    try {
        if ($action === 'approve') {
            // Update request status
            $stmt = $pdo->prepare("UPDATE swap_requests SET status = 'approved' WHERE id = ?");
            $stmt->execute([$request_id]);
            $message = "Request approved successfully!";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE swap_requests SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$request_id]);
            $message = "Request rejected successfully!";
        }
        
        // Refresh the requests list
        $stmt = $pdo->query("
            SELECT 
                sr.id,
                sr.doctor_id,
                sr.shift_date,
                sr.shift_type,
                sr.status,
                sr.created_at,
                d.name AS doctor_name
            FROM 
                swap_requests sr
            JOIN 
                doctors d ON sr.doctor_id = d.id
            WHERE 
                sr.status = 'pending'
            ORDER BY 
                sr.created_at DESC
        ");
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shift Requests - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background: linear-gradient(135deg, #6a11cb, #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        } 
        .request-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .request-approved {
            border-left-color: #198754;
        }
        .request-rejected {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Shift Requests</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= strpos($message, 'Error') !== false ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info">No pending shift requests found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $request): ?>
            <div class="col-md-6">
                <div class="card request-card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Request #<?= htmlspecialchars($request['id']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <p>
                                    <strong>Doctor:</strong> <?= htmlspecialchars($request['doctor_name']) ?><br>
                                    <strong>Date:</strong> <?= htmlspecialchars($request['shift_date']) ?><br>
                                    <strong>Shift Type:</strong> <?= htmlspecialchars(ucfirst($request['shift_type'])) ?><br>
                                    <strong>Status:</strong> <?= htmlspecialchars(ucfirst($request['status'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">
                                Requested on: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($request['created_at']))) ?>
                            </small>
                            <div>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-success me-2">Approve</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
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