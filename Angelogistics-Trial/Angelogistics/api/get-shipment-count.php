<?php
header('Content-Type: application/json');
require '../db.php';

$stmt = $pdo->query("SELECT COUNT(*) as count FROM shipment");
echo json_encode($stmt->fetch());
?>