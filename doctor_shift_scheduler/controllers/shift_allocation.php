<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/notification_helper.php';

function allocateShifts() {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Futa shifts za wiki ijayo
        $nextMonday = date('Y-m-d', strtotime('next monday'));
        $nextSunday = date('Y-m-d', strtotime('next sunday'));
        
        $pdo->prepare("DELETE FROM shifts WHERE shift_date BETWEEN ? AND ?")
            ->execute([$nextMonday, $nextSunday]);
        
        // Pata madaktari wote wanaofanya kazi
        $doctors = $pdo->query("SELECT id, name FROM doctors WHERE active = 1")->fetchAll();
        
        // Bainisha aina za shifts na mahitaji
        $shiftTypes = ['asubuhi', 'mchana', 'usiku'];
        $doctorsPerShift = [
            'asubuhi' => 2,
            'mchana' => 2,
            'usiku' => 1
        ];
        
        // Pangia shifts kwa kila siku ya wiki
        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime("$nextMonday + $i days"));
            
            foreach ($shiftTypes as $shift) {
                $required = $doctorsPerShift[$shift];
                $assigned = 0;
                
                // Gawanya madaktari kwa shift hii
                foreach ($doctors as $doctor) {
                    if ($assigned >= $required) break;
                    
                    // Angalia kama daktari tayari ana shift siku hii
                    $stmt = $pdo->prepare("SELECT id FROM shifts WHERE doctor_id = ? AND shift_date = ?");
                    $stmt->execute([$doctor['id'], $currentDate]);
                    
                    if ($stmt->rowCount() == 0) {
                        // Panga shift
                        $pdo->prepare("INSERT INTO shifts (doctor_id, shift_date, shift_type) VALUES (?, ?, ?)")
                            ->execute([$doctor['id'], $currentDate, $shift]);
                        
                        $assigned++;
                        
                        // Tumia arifa
                        sendShiftNotification($doctor['id'], $currentDate, $shift);
                    }
                }
            }
        }
        
        $pdo->commit();
        return "Shifti zimepangwa kikamilifu kwa wiki inayoanza $nextMonday";
    } catch (Exception $e) {
        $pdo->rollBack();
        return "Kosa katika upangaji wa shifti: " . $e->getMessage();
    }
}
?>