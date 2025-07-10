<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="training_export.csv"');

// UTF-8 BOM for Excel
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// CSV header
fputcsv($output, [
    'Name', 'Email', 'Type', 'Title',
    'Start Date', 'End Date', 'Hours', 'Institution',
    'Status', 'File Names', 'Impact Rating', 'Impact Comments'
]);

// Query main table
$stmt = $pdo->query("
    SELECT te.*, ia.rating, ia.comments
    FROM training_entries te
    LEFT JOIN impact_assessments ia ON te.id = ia.training_entry_id
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stmt2 = $pdo->prepare("SELECT file_name FROM supporting_docs WHERE training_entry_id = ?");
    $stmt2->execute([$row['id']]);
    $files = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    $fileNames = implode(', ', $files);

    $start_date = (!empty($row['start_date']) && strtotime($row['start_date'])) ? date('Y-m-d', strtotime($row['start_date'])) : '';
    $end_date = (!empty($row['end_date']) && strtotime($row['end_date'])) ? date('Y-m-d', strtotime($row['end_date'])) : '';

    fputcsv($output, [
        $row['staff_name'] ?? '',
        $row['staff_email'] ?? '',
        $row['staff_type'] ?? '',
        $row['title'] ?? '',
        $start_date,
        $end_date,
        $row['hours'] ?? '',
        $row['institution'] ?? '',
        $row['status'] ?? '',
        $fileNames,
        $row['rating'] ?? '',
        $row['comments'] ?? '',
    ]);
}

fclose($output);
exit;
