<?php
require_once __DIR__ . '/../../config/db.php';

session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Verify admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = trim($_POST['phone']);
        $specialization = trim($_POST['specialization']);
        $role = $_POST['role'] ?? 'doctor';
        
        try {
            $stmt = $pdo->prepare("INSERT INTO doctors (name, email, password, phone, specialization, role) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $phone, $specialization, $role]);
            $_SESSION['success'] = "Daktari amesajiliwa kikamilifu!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Hitilafu: " . $e->getMessage();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    }
    elseif (isset($_POST['edit_doctor'])) {
        $doctorId = $_POST['doctor_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $specialization = trim($_POST['specialization']);
        $role = $_POST['role'];
        
        try {
            $stmt = $pdo->prepare("UPDATE doctors SET 
                                  name = ?, email = ?, phone = ?, 
                                  specialization = ?, role = ?
                                  WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $specialization, $role, $doctorId]);
            
            $_SESSION['success'] = "Taarifa za daktari zimebadilishwa!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Hitilafu: " . $e->getMessage();
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        }
    }
    elseif (isset($_POST['toggle_status'])) {
        $doctorId = $_POST['doctor_id'];
        
        // Get current status first to ensure accuracy
        $stmt = $pdo->prepare("SELECT is_active FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        $currentStatus = $stmt->fetchColumn();
        
        // Toggle status
        $newStatus = $currentStatus ? 0 : 1;
        
        $stmt = $pdo->prepare("UPDATE doctors SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $doctorId]);
        
        $_SESSION['success'] = "Hali ya daktari imebadilishwa";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    elseif (isset($_POST['delete_doctor'])) {
        $doctorId = $_POST['doctor_id'];
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$doctorId]);
        
        $_SESSION['success'] = "Daktari amefutwa kikamilifu";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    elseif (isset($_POST['change_password'])) {
        $doctorId = $_POST['doctor_id'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE doctors SET password = ? WHERE id = ?");
        $stmt->execute([$newPassword, $doctorId]);
        
        $_SESSION['success'] = "Nenosiri limebadilishwa kikamilifu";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Get doctor list
$doctors = $pdo->query("SELECT * FROM doctors ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Display session messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usimamizi wa Madaktari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .status-badge {
            width: 80px;
        }
        .action-btns {
            white-space: nowrap;
        }
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body >
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Usimamizi wa Madaktari</h2>
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="btn-custom-secondary"></i> Rudi kwenye Dashibodi
                </a>
            </div>

            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Add doctor form -->
                <form method="POST" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Jina" required>
                        </div>
                        <div class="col-md-3">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-md-2">
                            <input type="password" name="password" class="form-control" placeholder="Nenosiri" required minlength="8">
                        </div>
                        <div class="col-md-2">
                            <input type="tel" name="phone" class="form-control" placeholder="Simu" pattern="[0-9]{9,15}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_doctor" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Sajili
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2 g-3">
                        <div class="col-md-4">
                            <input type="text" name="specialization" class="form-control" placeholder="Specialization">
                        </div>
                        <div class="col-md-2">
                            <select name="role" class="form-select" required>
                                <option value="doctor">Daktari</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Doctors list -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Jina</th>
                                <th>Email</th>
                                <th>Simu</th>
                                <th>Specialization</th>
                                <th>Hali</th>
                                <th class="action-btns">Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?= $doctor['id'] ?></td>
                                <td><?= htmlspecialchars($doctor['name']) ?></td>
                                <td><?= htmlspecialchars($doctor['email']) ?></td>
                                <td><?= !empty($doctor['phone']) ? htmlspecialchars($doctor['phone']) : '-' ?></td>
                                <td><?= !empty($doctor['specialization']) ? htmlspecialchars($doctor['specialization']) : '-' ?></td>
                                <td>
                                    <span class="badge <?= $doctor['is_active'] ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                        <?= $doctor['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm <?= $doctor['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                            <i class="fas fa-power-off"></i> <?= $doctor['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                            data-bs-target="#editModal<?= $doctor['id'] ?>">
                                        <i class="fas fa-edit"></i> Hariri
                                    </button>
                                    
                                    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal"
                                            data-bs-target="#passwordModal<?= $doctor['id'] ?>">
                                        <i class="fas fa-key"></i> Nenosiri
                                    </button>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Una uhakika unataka kufuta daktari huyu?')">
                                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                                        <button type="submit" name="delete_doctor" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Futa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php foreach ($doctors as $doctor): ?>
    <div class="modal fade" id="editModal<?= $doctor['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Hariri Daktari</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Jina Kamili</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($doctor['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Barua Pepe</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($doctor['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Namba ya Simu</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control" 
                                   value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jukumu</label>
                            <select name="role" class="form-select" required>
                                <option value="doctor" <?= $doctor['role'] === 'doctor' ? 'selected' : '' ?>>Daktari</option>
                                <option value="admin" <?= $doctor['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Funga</button>
                        <button type="submit" name="edit_doctor" class="btn btn-primary">Hifadhi Mabadiliko</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Password Change Modal -->
    <div class="modal fade" id="passwordModal<?= $doctor['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Badilisha Nenosiri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nenosiri Jipya</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="newPass<?= $doctor['id'] ?>" 
                                       class="form-control" required minlength="8">
                                <span class="input-group-text password-toggle" 
                                      onclick="togglePassword('newPass<?= $doctor['id'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rudia Nenosiri</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirmPass<?= $doctor['id'] ?>" 
                                       class="form-control" required minlength="8">
                                <span class="input-group-text password-toggle" 
                                      onclick="togglePassword('confirmPass<?= $doctor['id'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Funga</button>
                        <button type="submit" name="change_password" class="btn btn-primary" 
                                onclick="return validatePassword(<?= $doctor['id'] ?>)">
                            Hifadhi Nenosiri
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Phone number formatting
        document.querySelectorAll('input[type="tel"]').forEach(function(input) {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
        
        // Toggle password visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Validate password match
        function validatePassword(id) {
            const newPass = document.getElementById('newPass' + id).value;
            const confirmPass = document.getElementById('confirmPass' + id).value;
            
            if (newPass !== confirmPass) {
                alert('Nenosiri halifanani! Tafadhali hakikisha uliingiza nenosiri sawa katika sehemu zote mbili.');
                return false;
            }
            
            if (newPass.length < 8) {
                alert('Nenosiri lazima liwe na herufi 8 au zaidi.');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>