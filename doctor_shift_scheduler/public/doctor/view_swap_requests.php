<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$doctor_id = $_SESSION['user_id'];
$message = '';
$swaps = [];

try {
    // Get all swap requests for this doctor (both initiated and received)
    $stmt = $pdo->prepare("
        SELECT 
            ss.id AS swap_id,
            ss.status,
            ss.created_at,
            ss.doctor_id,
            ss.target_doctor_id,
            ss.requested_shift_id,
            d1.name AS requesting_doctor_name,
            d2.name AS target_doctor_name,
            s.shift_date,
            s.shift_type,
            s2.shift_date AS target_shift_date,
            s2.shift_type AS target_shift_type,
            CASE 
                WHEN ss.doctor_id = ? THEN 'requested'
                ELSE 'received'
            END AS request_type
        FROM shift_swaps ss
        JOIN doctors d1 ON ss.doctor_id = d1.id
        JOIN doctors d2 ON ss.target_doctor_id = d2.id
        JOIN shifts s ON ss.requested_shift_id = s.id
        LEFT JOIN shifts s2 ON (
            s2.doctor_id = ss.target_doctor_id 
            AND s2.shift_date = s.shift_date
            AND s2.shift_type = s.shift_type
        )
        WHERE ss.doctor_id = ? OR ss.target_doctor_id = ?
        ORDER BY ss.created_at DESC
    ");
    $stmt->execute([$doctor_id, $doctor_id, $doctor_id]);
    $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Maombi Yangu ya Kubadilisha Shifti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background: linear-gradient(135deg, #6a11ca, #0d47a1);
        
        } 
        .swap-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
        }
        .swap-approved { border-left-color: #198754; }
        .swap-rejected { border-left-color: #dc3545; }
        .badge-type { font-size: 0.85em; }
        .requested-badge { background-color: #6c757d; }
        .received-badge { background-color: #0dcaf0; }
    </style>
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Maombi Yangu ya Kubadilisha Shifti</h2>
        <a href="../doctor/dashboard.php" class="btn btn-outline-primary">Rudi kwenye Dashibodi</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($swaps)): ?>
        <div class="alert alert-info">Huna maombi yoyote ya kubadilisha shifti kwa sasa.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($swaps as $swap): ?>
            <div class="col-md-6">
                <div class="card swap-card <?= $swap['status'] === 'approved' ? 'swap-approved' : 
                                           ($swap['status'] === 'rejected' ? 'swap-rejected' : '') ?>">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">Ombi #<?= htmlspecialchars($swap['swap_id']) ?></h5>
                        <span class="badge <?= $swap['request_type'] === 'requested' ? 'requested-badge' : 'received-badge' ?>">
                            <?= $swap['request_type'] === 'requested' ? 'Ulitoa' : 'Ulipokea' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Shifti ya Awali</h6>
                                <p>
                                    <strong>Daktari:</strong> <?= htmlspecialchars($swap['requesting_doctor_name']) ?><br>
                                    <strong>Tarehe:</strong> <?= htmlspecialchars($swap['shift_date']) ?><br>
                                    <strong>Aina:</strong> <span class="badge bg-info text-dark badge-type"><?= htmlspecialchars(ucfirst($swap['shift_type'])) ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Shifti Inayohusika</h6>
                                <p>
                                    <strong>Daktari:</strong> <?= htmlspecialchars($swap['target_doctor_name']) ?><br>
                                    <strong>Tarehe:</strong> <?= htmlspecialchars($swap['target_shift_date'] ?? 'N/A') ?><br>
                                    <strong>Aina:</strong> <span class="badge bg-info text-dark badge-type"><?= htmlspecialchars(ucfirst($swap['target_shift_type'] ?? 'N/A')) ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-<?= $swap['status'] === 'approved' ? 'success' : 
                                                       ($swap['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars(ucfirst($swap['status'])) ?>
                                </span>
                                <small class="text-muted ms-2">
                                    Tarehe: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($swap['created_at']))) ?>
                                </small>
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