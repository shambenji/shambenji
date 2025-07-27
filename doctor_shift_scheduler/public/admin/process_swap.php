<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Verify admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Validate input
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    header('Location: view_swap_requests.php?error=Invalid%20request');
    exit;
}

$action = $_GET['action'];
$swap_id = (int)$_GET['id'];

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    header('Location: view_swap_requests.php?error=Invalid%20action');
    exit;
}

try {
    // Update the swap status
    $stmt = $pdo->prepare("UPDATE shift_swaps SET status = ? WHERE id = ?");
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt->execute([$new_status, $swap_id]);
    
    // If approved, update the shifts in the database
    if ($action === 'approve') {
        // Get swap details
        $swap_stmt = $pdo->prepare("
            SELECT doctor_id, target_doctor_id, requested_shift_id 
            FROM shift_swaps 
            WHERE id = ?
        ");
        $swap_stmt->execute([$swap_id]);
        $swap = $swap_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update the shifts (implementation depends on your DB structure)
        // This is a basic example - adjust according to your needs
        $update_stmt = $pdo->prepare("
            UPDATE shifts 
            SET doctor_id = ? 
            WHERE id = ?
        ");
        $update_stmt->execute([$swap['target_doctor_id'], $swap['requested_shift_id']]);
    }
    
    header('Location: view_swap_requests.php?success=Swap%20'.urlencode($action).'d');
    exit;
    
} catch (PDOException $e) {
    header('Location: view_swap_requests.php?error=Database%20error');
    exit;
}