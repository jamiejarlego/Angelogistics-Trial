<?php
header('Content-Type: application/json');
require '../db.php';

// Get total number of shipments
$total = $pdo->query("SELECT COUNT(*) FROM shipment")->fetchColumn();

// Join shipment with importer to calculate percentage per importer name
$stmt = $pdo->query("
    SELECT i.importer_name, COUNT(*) * 100.0 / $total AS percentage
    FROM shipment s
    JOIN importer i ON s.importer_id = i.importer_id
    GROUP BY s.importer_id, i.importer_name
");

$data = $stmt->fetchAll();
echo json_encode([
    'importers' => array_column($data, 'importer_name'),
    'percentages' => array_map('floatval', array_column($data, 'percentage')),
]);
?>
