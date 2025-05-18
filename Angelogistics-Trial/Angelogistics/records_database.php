<?php
// Database credentials (Replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "acl2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch shipment data (for flexibility)
function getShipmentData($conn, $filter_year, $sort_column, $sort_order) {
    // SQL query to fetch data from the shipment table with dynamic sorting
    $sql = "SELECT
                shipment.shipment_id,
                shipment.date_created,
                encoding_phase.encoding_phase_name,
                delivery_phase.delivery_phase_name,
                importer.importer_name,
                commodity.commodity_name,
                shipment.number_of_containers,
                container_deposit_status.container_deposit_status_name AS cd_status,
                shipping_line.shipping_line_name,
                shipment.eta,
                GROUP_CONCAT(container.container_number) AS container_numbers
            FROM shipment
            LEFT JOIN importer ON shipment.importer_id = importer.importer_id
            LEFT JOIN commodity ON shipment.commodity_id = commodity.commodity_id
            LEFT JOIN shipping_line ON shipment.shipping_line_id = shipping_line.shipping_line_id
            LEFT JOIN encoding_phase ON shipment.encoding_phase_id = encoding_phase.encoding_phase_id
            LEFT JOIN delivery_phase ON shipment.delivery_phase_id = delivery_phase.delivery_phase_id
            LEFT JOIN container ON shipment.shipment_id = container.shipment_id
            LEFT JOIN container_deposit_status ON shipment.container_deposit_status_id = container_deposit_status.container_deposit_status_id
            WHERE YEAR(shipment.date_created) = ?
            GROUP BY shipment.shipment_id
            ORDER BY $sort_column $sort_order, shipment.shipment_id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $filter_year);

    if (!$stmt->execute()) {
        die("Query failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    return $result;
}
?>