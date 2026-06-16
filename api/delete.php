<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $type = $data['type'] ?? '';
    $id = $data['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing ID']);
        exit;
    }
    
    try {
        if ($type === 'assistant') {
            $stmt = $pdo->prepare("DELETE FROM assistants WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } elseif ($type === 'schedule') {
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid type']);
        }
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
