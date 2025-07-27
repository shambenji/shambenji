<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$message = '';
$swaps = [];

try {
    // Pata orodha ya maombi yote ya kubadilisha shifti
    $stmt = $pdo->query("
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
            s2.shift_type AS target_shift_type
        FROM shift_swaps ss
        JOIN doctors d1 ON ss.doctor_id = d1.id
        JOIN doctors d2 ON ss.target_doctor_id = d2.id
        JOIN shifts s ON ss.requested_shift_id = s.id
        LEFT JOIN shifts s2 ON (
            s2.doctor_id = ss.target_doctor_id 
            AND s2.shift_date = s.shift_date
            AND s2.shift_type = s.shift_type
        )
        ORDER BY ss.created_at DESC
    ");
    $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Hitilafu ya database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orodha ya Maombi ya Kubadilisha Shifti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background: linear-gradient(135deg, #6a11cb, #0d47a1);
            min-height: 100vh;
        
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
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }
        .swap-approved {
            border-left-color: #198754;
        }
        .swap-rejected {
            border-left-color: #dc3545;
        }
        .badge-type {
            font-size: 0.85em;
        }
    </style>
</head>
<body class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Orodha ya Maombi ya Kubadilisha Shifti</h2>
        <div class="d-flex justify-content-between align-items-center mb-4">
        
        <a href="dashboard.php" class="btn-custom-secondary">Back to Dashboard</a>
    </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($swaps)): ?>
        <div class="alert alert-info">Hakuna maombi ya kubadilisha shifti kwa sasa.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($swaps as $swap): ?>
            <div class="col-md-6">
                <div class="card swap-card <?= $swap['status'] === 'approved' ? 'swap-approved' : ($swap['status'] === 'rejected' ? 'swap-rejected' : '') ?>">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ombi #<?= htmlspecialchars($swap['swap_id']) ?></h5>
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
                                <h6>Shifti Inayotaka Kubadilishwa</h6>
                                <p>
                                    <strong>Daktari:</strong> <?= htmlspecialchars($swap['target_doctor_name']) ?><br>
                                    <strong>Tarehe:</strong> <?= htmlspecialchars($swap['target_shift_date'] ?? 'N/A') ?><br>
                                    <strong>Aina:</strong> <span class="badge bg-info text-dark badge-type"><?= htmlspecialchars(ucfirst($swap['target_shift_type'] ?? 'N/A')) ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-<?= $swap['status'] === 'approved' ? 'success' : ($swap['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars(ucfirst($swap['status'])) ?>
                                </span>
                                <small class="text-muted ms-2">
                                    Tarehe: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($swap['created_at']))) ?>
                                </small>
                            </div>
                            <?php if ($swap['status'] === 'pending'): ?>
                                <div>
                                    <a href="process_swap.php?action=approve&id=<?= $swap['swap_id'] ?>" class="btn btn-sm btn-success">Kubali</a>
                                    <a href="process_swap.php?action=reject&id=<?= $swap['swap_id'] ?>" class="btn btn-sm btn-danger">Kataa</a>
                                </div>
                            <?php endif; ?>
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