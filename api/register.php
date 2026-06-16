<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nim = $data['nim'] ?? null;
    $name = $data['name'] ?? null;
    $role = $data['role'] ?? 'assistant';
    $division_id = $data['division_id'] ?? null;
    $phone = $data['phone'] ?? null;
    $face_data = $data['face_data'] ?? null; // JSON string of the float array
    
    if (!$nim || !$name || !$face_data) {
        http_response_code(400);
        echo json_encode(["error" => "nim, name, and face_data are required"]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO assistants (nim, name, role, division_id, phone, face_data) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nim, $name, $role, $division_id, $phone, $face_data]);
        
        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
    } catch (\PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            http_response_code(409);
            echo json_encode(["error" => "NIM already exists"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
