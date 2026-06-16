<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT s.id, s.assistant_id, s.day_of_week, s.start_time, s.end_time, a.name as assistant_name, a.nim,
               (SELECT COUNT(*) FROM attendance_logs al WHERE al.assistant_id = s.assistant_id AND DATE(al.timestamp) = ?) as has_attended_today
        FROM schedules s 
        JOIN assistants a ON s.assistant_id = a.id 
        ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
    ");
    $stmt->execute([$date]);
    $schedules = $stmt->fetchAll();
    echo json_encode($schedules);
}  
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? null;
    $assistant_id = $data['assistant_id'] ?? null;
    $day_of_week = $data['day_of_week'] ?? null;
    $start_time = $data['start_time'] ?? null;
    $end_time = $data['end_time'] ?? null;
    
    if (!$assistant_id || !$day_of_week || !$start_time || !$end_time) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE schedules SET assistant_id=?, day_of_week=?, start_time=?, end_time=? WHERE id=?");
            $stmt->execute([$assistant_id, $day_of_week, $start_time, $end_time, $id]);
            echo json_encode(["success" => true, "updated" => true]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO schedules (assistant_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$assistant_id, $day_of_week, $start_time, $end_time]);
            echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
