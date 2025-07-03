<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="training_export.csv"');

$output = fopen('php://output', 'w');

// CSV header
fputcsv($output, [
    'Name', 'Email', 'Type', 'Title',
    'Start Date', 'End Date', 'Hours', 'Institution',
    'Status', 'File Name', 'Impact Rating', 'Impact Comments'
]);

// Query with LEFT JOINs
$stmt = $pdo->query("
    SELECT 
        te.*, sd.file_name, ia.rating, ia.comments
    FROM training_entries te
    LEFT JOIN supporting_docs sd ON te.id = sd.training_entry_id
    LEFT JOIN impact_assessments ia ON te.id = ia.training_entry_id
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['staff_name'],
        $row['staff_email'],
        $row['staff_type'],
        $row['title'],
        $row['start_date'],
        $row['end_date'],
        $row['hours'],
        $row['institution'],
        $row['status'],
        $row['file_name'],
        $row['rating'],
        $row['comments']
    ]);
}

fclose($output);
exit;
?>
