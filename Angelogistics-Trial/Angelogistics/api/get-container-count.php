<?php
header('Content-Type: application/json');
require '../db.php';

$stmt = $pdo->query("SELECT SUM(number_of_containers) as count FROM shipment");
echo json_encode($stmt->fetch());
?>