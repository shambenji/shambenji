<?php
require_once __DIR__ . '/../../config/db.php';

class ScheduleRules {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // 1. Panga ratiba kwa mwezi mzima
    public function generateSchedule($month, $year) {
        try {
            $this->pdo->beginTransaction();
            
            // Futa shifti zilizopangwa otomatiki kwa mwezi huu
            $this->pdo->prepare("DELETE FROM shifts 
                               WHERE MONTH(shift_date) = ? AND YEAR(shift_date) = ? 
                               AND auto_scheduled = 1")
                     ->execute([$month, $year]);
            
            $doctors = $this->pdo->query("SELECT id FROM doctors")->fetchAll(PDO::FETCH_COLUMN);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $shiftTypes = ['morning', 'afternoon', 'night'];
            $doctorIndex = 0;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                
                // Panga shifti kwa siku za kazi pekee
                if (date('N', strtotime($date)) <= 5) {
                    foreach ($shiftTypes as $shiftType) {
                        $doctorId = $doctors[$doctorIndex % count($doctors)];
                        
                        // Ingiza shifti kwenye database
                        $stmt = $this->pdo->prepare("INSERT INTO shifts 
                                                    (doctor_id, shift_date, shift_type, auto_scheduled) 
                                                    VALUES (?, ?, ?, 1)");
                        $stmt->execute([$doctorId, $date, $shiftType]);
                        
                        $doctorIndex++;
                    }
                }
            }
            
            $this->pdo->commit();
            return "Schedule for " . date('F Y', strtotime("$year-$month-01")) . " generated successfully!";
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return "Error: " . $e->getMessage();
        }
    }
    
    // 2. Hakiki upatikanaji wa daktari
    public function isDoctorAvailable($doctorId, $date) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM doctor_availability 
                                    WHERE doctor_id = ? AND date = ? AND available = 1");
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetchColumn() > 0;
    }
}
?>