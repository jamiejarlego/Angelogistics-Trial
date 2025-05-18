<?php
header('Content-Type: application/json');
require '../db.php';

$stmt = $pdo->query("
    SELECT 
        SUM(container_deposit_status_id = 2) AS cd,
        SUM(container_deposit_status_id = 3) AS cdCollected,
        SUM(container_deposit_status_id = 1) AS noCd
    FROM shipment
");

echo json_encode($stmt->fetch());
?>
