<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/schedule_rules.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 1. Panga ratiba otomatiki kwa mwezi ujao
if (isset($_POST['auto_schedule'])) {
    $month = date('m') + 1;
    $year = date('Y');
    
    if ($month > 12) {
        $month = 1;
        $year++;
    }
    
    $scheduler = new ScheduleRules($pdo);
    $result = $scheduler->generateSchedule($month, $year);
}

// 2. Gawanya shifti manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_allocation'])) {
    $doctorId = $_POST['doctor_id'];
    $shiftDate = $_POST['shift_date'];
    $shiftType = $_POST['shift_type'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO shifts (doctor_id, shift_date, shift_type, auto_scheduled) 
                             VALUES (?, ?, ?, 0)");
        $stmt->execute([$doctorId, $shiftDate, $shiftType]);
        $success = "Shift allocated successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Pata data ya kuonyesha
$doctors = $pdo->query("SELECT id, name FROM doctors")->fetchAll();

// Get shifts grouped by week
$shiftsByWeek = [];
$shiftsData = $pdo->query("SELECT s.*, d.name as doctor_name 
                          FROM shifts s 
                          JOIN doctors d ON s.doctor_id = d.id 
                          ORDER BY s.shift_date ASC")->fetchAll();

// Organize shifts by week
foreach ($shiftsData as $shift) {
    $weekNumber = date('W', strtotime($shift['shift_date']));
    $year = date('Y', strtotime($shift['shift_date']));
    $weekKey = $year . '-W' . $weekNumber;
    
    if (!isset($shiftsByWeek[$weekKey])) {
        $shiftsByWeek[$weekKey] = [
            'start_date' => date('Y-m-d', strtotime($shift['shift_date'] . ' -' . date('w', strtotime($shift['shift_date'])) . ' days')),
            'end_date' => date('Y-m-d', strtotime($shift['shift_date'] . ' +' . (6 - date('w', strtotime($shift['shift_date']))) . ' days')),
            'shifts' => []
        ];
    }
    
    $shiftsByWeek[$weekKey]['shifts'][] = $shift;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shift Allocation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .week-header {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 10px;
            margin: 20px 0 10px 0;
        }
        .table-responsive {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>Shift Allocation</h2>
                
                <!-- Automatic Scheduling Section -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5>Automatic Scheduling</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($result)): ?>
                            <div class="alert alert-success"><?= $result ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <p>Panga ratiba otomatiki:</p>
                                    <ul>
                                        <li>Hakikisha data ya upatikanaji wa madaktari imeandaliwa</li>
                                        <li>Mfumo utazingatia mizani ya mzigo kazi</li>
                                    </ul>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" name="auto_schedule" class="btn btn-success btn-lg">
                                        <i class="bi bi-robot"></i> Auto Schedule 
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Manual Shift Allocation Section -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5>Manual Shift Allocation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Doctor</label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="">Select Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Shift Date</label>
                                    <input type="date" name="shift_date" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Shift Type</label>
                                    <select name="shift_type" class="form-select" required>
                                        <option value="morning">Morning</option>
                                        <option value="afternoon">Afternoon</option>
                                        <option value="night">Night</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="manual_allocation" class="btn btn-primary">
                                <i class="bi bi-save"></i> Allocate Shift
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Scheduled Shifts by Week Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Scheduled Shifts by Week</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($shiftsByWeek)): ?>
                            <div class="alert alert-info">No shifts scheduled yet.</div>
                        <?php else: ?>
                            <?php foreach ($shiftsByWeek as $weekKey => $weekData): ?>
                                <div class="week-header">
                                    <h5>
                                        Week <?= date('W', strtotime($weekData['start_date'])) ?>: 
                                        <?= date('M j', strtotime($weekData['start_date'])) ?> - 
                                        <?= date('M j, Y', strtotime($weekData['end_date'])) ?>
                                    </h5>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Shift Type</th>
                                                <th>Doctor</th>
                                                <th>Allocation Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($weekData['shifts'] as $shift): ?>
                                            <tr>
                                                <td><?= date('m/d/Y', strtotime($shift['shift_date'])) ?></td>
                                                <td><?= date('D', strtotime($shift['shift_date'])) ?></td>
                                                <td><?= ucfirst($shift['shift_type']) ?></td>
                                                <td><?= htmlspecialchars($shift['doctor_name']) ?></td>
                                                <td>
                                                    <?= ($shift['auto_scheduled']) ? 
                                                        '<span class="badge bg-success">Auto</span>' : 
                                                        '<span class="badge bg-primary">Manual</span>' ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>