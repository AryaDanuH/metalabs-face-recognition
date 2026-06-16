<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT count(*) as active FROM assistants");
$active = $stmt->fetch()['active'];

echo json_encode([
    "activeAssistants" => $active,
    "attendanceRate" => "98.2%",
    "pendingAbsences" => 3
]);
?>
