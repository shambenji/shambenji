<?php
require_once __DIR__ . '/../config/db.php';

function requestShiftSwap($doctorId, $currentShiftId, $desiredShiftId) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Verify shifts exist
        $currentShift = $pdo->prepare("SELECT * FROM shifts WHERE id = ? AND doctor_id = ?")
                           ->execute([$currentShiftId, $doctorId])
                           ->fetch();
        
        $desiredShift = $pdo->prepare("SELECT * FROM shifts WHERE id = ? AND doctor_id != ?")
                           ->execute([$desiredShiftId, $doctorId])
                           ->fetch();
        
        if (!$currentShift || !$desiredShift) {
            throw new Exception("Invalid shift selection");
        }
        
        // Create swap request
        $stmt = $pdo->prepare("INSERT INTO shift_swaps (requester_id, current_shift_id, desired_shift_id, status) 
                              VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$doctorId, $currentShiftId, $desiredShiftId]);
        
        $pdo->commit();
        return "Swap request submitted successfully";
    } catch (Exception $e) {
        $pdo->rollBack();
        return "Error requesting swap: " . $e->getMessage();
    }
}

function approveShiftSwap($swapId) {
    // Similar detailed implementation for approval
}
?>