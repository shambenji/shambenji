<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctorId = $_SESSION['user_id'];

// Pata shifti za daktari
$stmt = $pdo->prepare("SELECT id, shift_date, shift_type FROM shifts WHERE doctor_id = ? ORDER BY shift_date DESC");
$stmt->execute([$doctorId]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shifti Zako</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Shifti Zako</h2>
            <a href="dashboard.php" class="btn btn-secondary">Rudi Nyuma</a>
        </div>
        
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Tarehe</th>
                    <th>Aina ya Shifti</th>
                    <th>Vitendo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($shift['shift_date'])) ?></td>
                        <td><?= ucfirst($shift['shift_type']) ?></td>
                        <td>
                            <a href="request_swap.php?shift_id=<?= $shift['id'] ?>" class="btn btn-sm btn-primary">
                                Omba Kubadilisha
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>