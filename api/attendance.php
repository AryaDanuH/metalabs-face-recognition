<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get recent attendance logs
    $stmt = $pdo->query("
        SELECT al.id, al.timestamp, al.method, al.status, a.name, a.nim 
        FROM attendance_logs al 
        JOIN assistants a ON al.assistant_id = a.id 
        ORDER BY al.timestamp DESC 
        LIMIT 10
    ");
    $logs = $stmt->fetchAll();
    echo json_encode($logs);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log attendance
    $data = json_decode(file_get_contents('php://input'), true);
    $assistant_id = $data['assistant_id'] ?? null;
    $method = $data['method'] ?? 'face_id';
    
    if (!$assistant_id) {
        http_response_code(400);
        echo json_encode(["error" => "assistant_id is required"]);
        exit;
    }
    
    date_default_timezone_set('Asia/Jakarta');
    $todayName = date('l');
    $currentTotalMinutes = (int)date('H') * 60 + (int)date('i');
    
    // Check schedules
    $stmt = $pdo->prepare("SELECT start_time, end_time FROM schedules WHERE assistant_id = ? AND day_of_week = ?");
    $stmt->execute([$assistant_id, $todayName]);
    $schedules = $stmt->fetchAll();
    
    if (empty($schedules)) {
        http_response_code(400);
        echo json_encode(["error" => "You are not scheduled for a shift today."]);
        exit;
    }
    
    $activeShiftFound = false;
    $status = 'on_time';
    
    foreach ($schedules as $s) {
        $startParts = explode(':', $s['start_time']);
        $endParts = explode(':', $s['end_time']);
        $startMinutes = (int)$startParts[0] * 60 + (int)$startParts[1];
        $endMinutes = (int)$endParts[0] * 60 + (int)$endParts[1];
        
        // Scan window: 30 mins before start, until the shift ends
        if ($currentTotalMinutes >= ($startMinutes - 30) && $currentTotalMinutes <= $endMinutes) {
            $activeShiftFound = true;
            if ($currentTotalMinutes > ($startMinutes + 15)) {
                $status = 'late';
            } else {
                $status = 'on_time';
            }
            break;
        }
    }
    
    if (!$activeShiftFound) {
        http_response_code(400);
        echo json_encode(["error" => "No active shift right now."]);
        exit;
    }
    
    // Check to prevent spam (already logged recently)
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) FROM attendance_logs 
        WHERE assistant_id = ? AND DATE(timestamp) = CURDATE() AND status != 'absent' 
        AND timestamp > DATE_SUB(NOW(), INTERVAL 4 HOUR)
    ");
    $checkStmt->execute([$assistant_id]);
    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(["error" => "You have already checked in recently."]);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO attendance_logs (assistant_id, method, status) VALUES (?, ?, ?)");
    $stmt->execute([$assistant_id, $method, $status]);
    
    echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
}
?>
