<?php
session_start();
// Hakiki kama mtumiaji ameingia na ni daktari
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';



// Weka vigezo mwanzo
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$pendingSwapRequestsCount = 0;
$upcomingShifts = [];
$pendingRequests = [];

try {
    // 1. Hesabu maombi yanayosubiri
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM shift_swaps 
        WHERE (doctor_id = ? OR target_doctor_id = ?) 
        AND status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $pendingSwapRequestsCount = $stmt->fetchColumn();

    // 2. Pata orodha kamili ya maombi
    $stmt = $pdo->prepare("
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
        WHERE (ss.doctor_id = ? OR ss.target_doctor_id = ?)
        AND ss.status = 'pending'
        ORDER BY ss.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Pata shifti 5 zinazokuja
    $stmt = $pdo->prepare("
        SELECT * 
        FROM shifts 
        WHERE doctor_id = ?
        AND shift_date >= CURDATE()
        ORDER BY shift_date ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcomingShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Hitilafu ya database: " . $e->getMessage();
}

//sms



class SmsSender {
    private $api_key;
    private $secret_key;

    public function __construct() {
        global $api_key, $secret_key;
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
    }

    public function sendSms($phone, $message) {
        // Weka logic ya kutuma SMS hapa
        return "SMS sent to $phone: $message (Test Mode)";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashibodi ya Daktari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background: linear-gradient(135deg, #6a11ca, #0d47a1);
        
        } 
        .card-border {
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }
        .badge-shift {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
        
    </style>
</head>
<body class="container py-4">
    <!-- Kichwa cha ukurasa -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Dashibodi ya Daktari</h2>
        <div>
            <a href="request_swap.php" class="btn btn-primary me-2">
                <i class="bi bi-arrow-left-right"></i> Omba Kubadilisha Shifti</a>
                <a href="view_swap_requests.php" class="btn btn-outline-primary me-2">Angalia Maombi ya Shifti</a>
                <a href="change_password.php" class="btn btn-outline-warning me-2">
            <i class="bi bi-key"></i> Badilisha Nenosiri </a>
            <a href="../logout.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Ondoka
            </a>
        </div>
    </div>

    <!-- Ujumbe wa taarifa -->
    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Kadi za Maelezo -->
    <div class="row">
        <!-- Kadi ya Maombi Yanayosubiri -->
        <div class="col-md-4">
            <div class="card card-border h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Maombi Yanayosubiri</h5>
                    <p class="display-4 mb-3"><?= $pendingSwapRequestsCount ?></p>
                    <a href="#pending-requests" class="btn btn-outline-primary btn-sm">
                        Angalia Yote <i class="bi bi-chevron-down"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Kadi ya Shifti Zinazokuja -->
        <div class="col-md-8">
            <div class="card card-border h-100">
                <div class="card-body">
                    <h5 class="card-title">Shifti Zinazokuja </h5>
                    <?php if ($upcomingShifts): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcomingShifts as $shift): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span>
                                    <?= htmlspecialchars($shift['shift_date']) ?>
                                </span>
                                <span class="badge bg-primary badge-shift">
                                    <?= htmlspecialchars(ucfirst($shift['shift_type'] ?? 'haiujulikani')) ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mt-3">Hakuna shifti zilizopangwa.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sehemu ya Maombi Yanayosubiri -->
    <div class="row mt-4" id="pending-requests">
        <div class="col-12">
            <div class="card card-border">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Maombi Yanayosubiri</h5>
                        <small class="text-muted">Jumla: <?= count($pendingRequests) ?></small>
                    </div>
                    
                    <?php if ($pendingRequests): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Kutoka kwa</th>
                                        <th>Kwenda kwa</th>
                                        <th>Tarehe</th>
                                        <th>Aina</th>
                                        <th>Iliombwa</th>
                                        <th>Vitendo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingRequests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['id']) ?></td>
                                        <td><?= htmlspecialchars($request['requesting_doctor']) ?></td>
                                        <td><?= htmlspecialchars($request['target_doctor']) ?></td>
                                        <td><?= htmlspecialchars($request['shift_date']) ?></td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= htmlspecialchars(ucfirst($request['shift_type'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($request['created_at']))) ?></td>
                                        <td>
                                            <?php if ($request['target_doctor_id'] == $_SESSION['user_id']): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="respond_swap.php?id=<?= $request['id'] ?>&action=approve" 
                                                       class="btn btn-success" title="Kubali">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <a href="respond_swap.php?id=<?= $request['id'] ?>&action=reject" 
                                                       class="btn btn-danger" title="Kataa">
                                                        <i class="bi bi-x-lg"></i>
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inasubiri majibu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            Hakuna maombi yanayosubiri kwa sasa.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Viungo vya Bootstrap na Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>