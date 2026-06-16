<?php
require_once 'db.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_history.csv"');

// Open the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('ID', 'NIM', 'Aslab Name', 'Method', 'Status', 'Timestamp'));

// Fetch all attendance logs
$stmt = $pdo->query("
    SELECT al.id, a.nim, a.name, al.method, al.status, al.timestamp 
    FROM attendance_logs al 
    JOIN assistants a ON al.assistant_id = a.id 
    ORDER BY al.timestamp DESC
");

// Loop over the rows, outputting them
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Format status nicely
    $status = ucfirst(str_replace('_', ' ', $row['status']));
    $method = $row['method'] === 'system' ? 'System (Auto)' : 'Face ID';
    
    fputcsv($output, array(
        $row['id'],
        $row['nim'],
        $row['name'],
        $method,
        $status,
        $row['timestamp']
    ));
}

fclose($output);
exit;
