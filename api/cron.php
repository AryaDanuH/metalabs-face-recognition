<?php
require_once 'db.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// using php datetime
date_default_timezone_set('Asia/Jakarta');

$todayName = date('l'); // Sunday, Monday, etc.
$currentHour = (int)date('H');
$currentMinute = (int)date('i');
$currentTotalMinutes = $currentHour * 60 + $currentMinute;

// Get all schedules for today
$stmt = $pdo->prepare("
    SELECT id, assistant_id, start_time, end_time 
    FROM schedules 
    WHERE day_of_week = ?
");
$stmt->execute([$todayName]);
$schedules = $stmt->fetchAll();

$inserted = 0;

foreach ($schedules as $s) {
    $endParts = explode(':', $s['end_time']);
    $endMinutes = (int)$endParts[0] * 60 + (int)$endParts[1];
    
    // Check if the shift has already ended
    if ($currentTotalMinutes > $endMinutes) {
        // Shift is over. Check if they attended today.
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM attendance_logs 
            WHERE assistant_id = ? AND DATE(timestamp) = CURDATE()
        ");
        $checkStmt->execute([$s['assistant_id']]);
        $hasAttended = $checkStmt->fetchColumn() > 0;
        
        if (!$hasAttended) {
            // Check if we ALREADY logged an absence for them today to prevent duplicates
            $checkAbsent = $pdo->prepare("
                SELECT COUNT(*) FROM attendance_logs 
                WHERE assistant_id = ? AND DATE(timestamp) = CURDATE() AND status = 'absent'
            ");
            $checkAbsent->execute([$s['assistant_id']]);
            $alreadyLoggedAbsent = $checkAbsent->fetchColumn() > 0;
            
            if (!$alreadyLoggedAbsent) {
                // Log absence
                $insertStmt = $pdo->prepare("
                    INSERT INTO attendance_logs (assistant_id, method, status) 
                    VALUES (?, 'system', 'absent')
                ");
                $insertStmt->execute([$s['assistant_id']]);
                $inserted++;
            }
        }
    }
}

echo json_encode(["status" => "success", "absences_logged" => $inserted]);
