<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Pata data ya ripoti
$monthlyShifts = $pdo->query("SELECT 
                             DATE_FORMAT(shift_date, '%Y-%m') as month, 
                             COUNT(*) as shift_count 
                             FROM shifts 
                             GROUP BY month 
                             ORDER BY month DESC")->fetchAll();

$swapStats = $pdo->query("SELECT 
                         status, 
                         COUNT(*) as count 
                         FROM shift_swaps 
                         GROUP BY status")->fetchAll();

$doctorShifts = $pdo->query("SELECT 
                            d.name, 
                            COUNT(s.id) as shift_count 
                            FROM doctors d 
                            LEFT JOIN shifts s ON d.id = s.doctor_id 
                            GROUP BY d.id 
                            ORDER BY shift_count DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>System Reports</h2>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Monthly Shifts Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="shiftsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Swap Requests Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="swapsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Doctors Shift Count</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Number of Shifts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctorShifts as $doctor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doctor['name']) ?></td>
                                    <td><?= $doctor['shift_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
    // Monthly shifts chart
    const shiftsCtx = document.getElementById('shiftsChart').getContext('2d');
    const shiftsChart = new Chart(shiftsCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($monthlyShifts, 'month')) ?>,
            datasets: [{
                label: 'Shifts per Month',
                data: <?= json_encode(array_column($monthlyShifts, 'shift_count')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Swap status chart
    const swapsCtx = document.getElementById('swapsChart').getContext('2d');
    const swapsChart = new Chart(swapsCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($swapStats, 'status')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($swapStats, 'count')) ?>,
                backgroundColor: [
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(255, 99, 132, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
    </script>
</body>
</html>