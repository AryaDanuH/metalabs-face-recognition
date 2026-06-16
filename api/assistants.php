<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT a.id, a.nim, a.name, a.role, a.face_data, d.name as division_name FROM assistants a LEFT JOIN divisions d ON a.division_id = d.id");
$assistants = $stmt->fetchAll();

echo json_encode($assistants);
?>
