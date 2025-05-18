<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require '../db.php';

$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    // Prepare the main query
    $stmt = $pdo->prepare("
        SELECT 
            s.shipment_id,
            ep.encoding_phase_name AS encoding_phase,
            dp.delivery_phase_name AS delivery_phase,
            i.importer_name AS importer,
            c.commodity_name AS commodity,
            s.number_of_containers,
            CASE s.container_deposit_status_id
                WHEN 1 THEN 'CD'
                WHEN 2 THEN 'NO CD'
                WHEN 3 THEN 'CD COLLECTED'
                ELSE 'UNKNOWN'
            END AS cd,
            sl.shipping_line_name AS shipping_line,
            s.eta,
            s.due_date
        FROM shipment s
        JOIN importer i ON s.importer_id = i.importer_id
        JOIN commodity c ON s.commodity_id = c.commodity_id
        JOIN shipping_line sl ON s.shipping_line_id = sl.shipping_line_id
        JOIN encoding_phase ep ON s.encoding_phase_id = ep.encoding_phase_id
        JOIN delivery_phase dp ON s.delivery_phase_id = dp.delivery_phase_id
        WHERE YEAR(s.eta) = ?
    ");
    $stmt->execute([$year]);
    $data = $stmt->fetchAll();

    // DEBUGGING: Print the first row of data to verify what fields are returned
    if (!empty($data)) {
        error_log("First row of data:\n" . print_r($data[0], true));
        // OR, temporarily output to browser for inspection
        // echo "<pre>" . print_r($data, true) . "</pre>";
    } else {
        error_log("No data returned for year {$year}");
    }

    // Response: output data as JSON
    echo json_encode($data);

    // Optional: for debugging, you could also output the raw PHP array (temporarily)
    // Uncomment below:
    // echo "<pre>";
    // print_r($data);
    // echo "</pre>";

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>