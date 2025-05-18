<?php
header('Content-Type: application/json');
require '../db.php';

$sql = "SELECT MONTHNAME(eta) as month, COUNT(*) as count FROM shipment GROUP BY MONTH(eta)";
$data = $pdo->query($sql)->fetchAll();

echo json_encode([
    'months' => array_column($data, 'month'),
    'counts' => array_column($data, 'count'),
]);
?>