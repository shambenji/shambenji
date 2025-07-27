<?php
// controllers/auth.php

require_once __DIR__ . '/../config/db.php';

function validatePhoneNumber($phone) {
    // Remove all non-digit characters
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid Tanzanian number
    if (strlen($cleaned) === 9 && in_array(substr($cleaned, 0, 2), ['65', '67', '68', '69', '71', '73', '74', '75', '76', '78', '79'])) {
        return '+255' . $cleaned;
    }
    
    // Check if it's already in +255 format
    if (strlen($cleaned) === 12 && strpos($cleaned, '255') === 0) {
        return '+' . $cleaned;
    }
    
    return false;
}

function register($name, $email, $password, $phone = null, $role = 'doctor') {
    global $pdo;

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Barua pepe si sahihi";
    }

    // Validate password strength
    if (strlen($password) < 8) {
        return "Nenosiri lazima liwe na herufi 8 au zaidi";
    }
    
    // Validate phone if provided
    if (!empty($phone)) {
        $validatedPhone = validatePhoneNumber($phone);
        if (!$validatedPhone) {
            return "Namba ya simu si sahihi. Tafadhali tumia mfano: 0712345678 au +255712345678";
        }
        $phone = $validatedPhone;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return "Barua pepe tayari imesajiliwa";
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO doctors (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $role]);
        
        return "Usajili umekamilika kikamilifu. Tafadhali ingia kwa maelezo yako.";
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return "Kosa la mfumo wakati wa usajili. Tafadhali jaribu tena baadaye.";
    }
}

function login($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        return true;
    }
    
    return false;
}
?>