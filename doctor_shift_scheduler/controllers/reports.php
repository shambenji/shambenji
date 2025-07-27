<?php
require_once __DIR__ . '/../config/db.php';

function getShiftSummary(PDO $db) {
    $stmt = $db->query("
        SELECT d.name, COUNT(s.id) AS total_shifts
        FROM shifts s
        JOIN doctors d ON s.doctor_id = d.id
        GROUP BY s.doctor_id
        ORDER BY total_shifts DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWeeklyAttendance(PDO $db) {
    $stmt = $db->query("
        SELECT d.name, s.shift_date, s.shift_type
        FROM shifts s
        JOIN doctors d ON s.doctor_id = d.id
        WHERE s.shift_date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
        ORDER BY s.shift_date DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
