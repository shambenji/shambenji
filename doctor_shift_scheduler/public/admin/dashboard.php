<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/auth.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get system statistics
$doctorsCount = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$pendingSwapsCount = $pdo->query("SELECT COUNT(*) FROM shift_swaps WHERE status = 'pending'")->fetchColumn();
$approvedSwaps = $pdo->query("SELECT COUNT(*) FROM shift_swaps WHERE status = 'approved'")->fetchColumn();
$totalShifts = $pdo->query("SELECT COUNT(*) FROM shifts")->fetchColumn();

// Initialize variables
$message = '';
$pendingSwaps = [];
$recentActivities = [];

try {
    // 1. Get pending shift swap requests
    $stmt = $pdo->query("
        SELECT 
            ss.id,
            ss.status,
            ss.created_at,
            ss.doctor_id,
            ss.target_doctor_id,
            ss.requested_shift_id,
            d1.name AS requesting_doctor,
            d2.name AS target_doctor,
            s.shift_date,
            s.shift_type
        FROM shift_swaps ss
        JOIN doctors d1 ON ss.doctor_id = d1.id
        JOIN doctors d2 ON ss.target_doctor_id = d2.id
        JOIN shifts s ON ss.requested_shift_id = s.id
        WHERE ss.status = 'pending'
        ORDER BY ss.created_at DESC
    ");
    $pendingSwaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get recent activities
    $stmt = $pdo->query("
        SELECT 
            id,
            doctor_id,
            shift_date,
            shift_type,
            created_at
        FROM shifts
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         
        body {
            background: linear-gradient(135deg, #6a11cb, #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        } 
        
        .card-highlight {
            border-left: 4px solid #0d6efd;
        }
        .badge-type {
            font-size: 0.85em;
        }
         /* Sidebar styling */
         .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.collapsed {
            transform: translateX(-250px);
        }
        
        .sidebar-toggle {
            position: fixed;
            left: 260px;
            top: 10px;
            z-index: 1100;
            transition: all 0.3s;
        }
        
        .sidebar-toggle.collapsed {
            left: 10px;
        }
        
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="allocate_shifts.php">Allocate Shifts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="manage_shifts.php">Manage Shifts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="manage_swaps.php">Manage Swaps</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="view_swap_requests.php">Swap Requests</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="user_management.php">Manage Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reports.php">Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h2">Admin Dashboard</h2>
                
                <div class="row my-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Doctors</h5>
                                <p class="card-text display-6"><?= htmlspecialchars($doctorsCount) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Pending Swaps</h5>
                                <p class="card-text display-6"><?= htmlspecialchars($pendingSwapsCount) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Approved Swaps</h5>
                                <p class="card-text display-6"><?= htmlspecialchars($approvedSwaps) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Shifts</h5>
                                <p class="card-text display-6"><?= htmlspecialchars($totalShifts) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <!-- Pending Swap Requests Section -->
                <div class="card card-highlight mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pending Shift Swap Requests</h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($pendingSwaps): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Doctor</th>
                                            <th>Swap With</th>
                                            <th>Shift Date</th>
                                            <th>Type</th>
                                            <th>Requested</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingSwaps as $swap): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($swap['id']) ?></td>
                                            <td><?= htmlspecialchars($swap['requesting_doctor']) ?></td>
                                            <td><?= htmlspecialchars($swap['target_doctor']) ?></td>
                                            <td><?= htmlspecialchars($swap['shift_date']) ?></td>
                                            <td>
                                                <span class="badge bg-info text-dark badge-type">
                                                    <?= htmlspecialchars(ucfirst($swap['shift_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($swap['created_at']))) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="process_swap.php?action=approve&id=<?= $swap['id'] ?>" class="btn btn-success">Approve</a>
                                                    <a href="process_swap.php?action=reject&id=<?= $swap['id'] ?>" class="btn btn-danger">Reject</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No pending swap requests at this time.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activities Section -->
                <div class="card card-highlight">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recentActivities): ?>
                            <ul class="list-group">
                                <?php foreach ($recentActivities as $activity): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Shift:</strong> <?= htmlspecialchars($activity['shift_date']) ?>
                                        <span class="badge bg-primary badge-type ms-2">
                                            <?= htmlspecialchars(ucfirst($activity['shift_type'])) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Added: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($activity['created_at']))) ?>
                                    </small>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No recent activities found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>