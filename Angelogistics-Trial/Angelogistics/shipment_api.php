<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$host = "localhost";
$username = "root";
$password = ""; // Default for XAMPP is no password
$dbname = "shipment__db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]));
}

$action = $_GET['action'];

if ($_GET['action'] === 'fetch') {
    $result = $conn->query("SELECT id, encoding_status, shipping_status, cd_status, importer, commodity, num_containers, shipping_line, eta, days_remaining FROM shipments");

    $shipments = [];
    while ($row = $result->fetch_assoc()) {
        $shipments[] = $row;
    }

    echo json_encode($shipments);
    exit;
}


if ($_GET['action'] === 'bulk_update') {
    $updates = json_decode(file_get_contents('php://input'), true);

    foreach ($updates as $update) {
        $id = intval($update['id']);
        $encoding = $conn->real_escape_string($update['encoding_status']);
        $shipping = $conn->real_escape_string($update['shipping_status']);
        $cd = $conn->real_escape_string($update['cd_status']);

        $sql = "UPDATE shipments SET encoding_status='$encoding', shipping_status='$shipping', cd_status='$cd' WHERE id=$id";
        $conn->query($sql);
    }

    echo json_encode(['success' => true]);
    exit;
}
// (Optional) You can later add "create", "update", "delete" actions if you want full CRUD.
if ($action == 'create') {
    $stmt = $conn->prepare("INSERT INTO shipments (encoding_status, shipping_status, importer, commodity, num_containers, cd_status, shipping_line, eta, days_remaining, bl_number, invoice_number, container_size, container_numbers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssissss",
        $_POST['encoding_status'],
        $_POST['shipping_status'],
        $_POST['importer'],
        $_POST['commodity'],
        $_POST['num_containers'],
        $_POST['cd_status'],
        $_POST['shipping_line'],
        $_POST['eta'],
        $_POST['days_remaining'],
        $_POST['bl_number'],
        $_POST['invoice_number'],
        $_POST['container_size'],
        $_POST['container_numbers']
    );
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    $stmt->close();
}

if ($action == 'delete') {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM shipments WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error deleting shipment"]);
    }
}

if ($action == 'importer_summary') {
    $result = $conn->query("SELECT importer, COUNT(*) as shipment_count FROM shipments GROUP BY importer");

    $summary = [];
    while ($row = $result->fetch_assoc()) {
        $summary[] = $row; // Contains 'importer' and 'shipment_count'
    }

    echo json_encode($summary);
    exit;
}

if ($_GET['action'] === 'count_active_shipments') {
    // Query to count all rows in the shipments table
    $query = "SELECT COUNT(*) AS total_shipments FROM shipments";  // No condition, counting all rows
    
    $result = $conn->query($query); // Execute the query
    
    if ($result) {
        $data = $result->fetch_assoc(); // Fetch the result
        
        // Return the total row count in JSON format
        echo json_encode(['total_shipments' => $data['total_shipments']]);
    } else {
        // Return an error if the query fails
        echo json_encode(["success" => false, "error" => "Error fetching shipment count"]);
    }
    exit; // End the script here
}
if ($_GET['action'] === 'count_total_containers') {
    // Query to sum the values in the 'num_containers' column
    $query = "SELECT SUM(num_containers) AS total_containers FROM shipments";  // Sum the num_containers column
    
    $result = $conn->query($query);  // Execute the query
    
    if ($result) {
        $data = $result->fetch_assoc();  // Fetch the result
        
        // Return the sum of num_containers in JSON format
        echo json_encode(['total_containers' => $data['total_containers']]);
    } else {
        // Return an error if the query fails
        echo json_encode(["success" => false, "error" => "Error fetching total containers count"]);
    }
    exit;  // End the script
}

if ($action === 'status_counts') {
    // Initialize counts
    $shipping_counts = [
        'SL/ADR/CD/WH' => 0,
        'FOR DELIVERY' => 0,
        'BILLED' => 0,
        'DELIVERED/FOR BILLING' => 0,
        'CONTAINER DEPOSIT' => 0,
        'NEW SHIPMENT' => 0,
    ];

    $encoding_counts = [
        'FOR CHECKING' => 0,
        'UPLOADED' => 0,
        'FOR UPLOADING' => 0,
        'FOR PRESAD' => 0,
        'FAN PAID' => 0,
        'FOR DT/PC' => 0,
    ];

       $cd_counts = [ 
        'CD' => 0,
        'CD COLLECTED' => 0,
        'NO CD' => 0
    ];

    // Fetch shipping_status counts (use correct table name: shipments)
    $sql_shipping = "SELECT shipping_status, COUNT(*) as count FROM shipments GROUP BY shipping_status";
    $result_shipping = $conn->query($sql_shipping);

    if ($result_shipping) {
        while ($row = $result_shipping->fetch_assoc()) {
            if (array_key_exists($row['shipping_status'], $shipping_counts)) {
                $shipping_counts[$row['shipping_status']] = (int)$row['count'];
            }
        }
    }

    // Fetch encoding_status counts
    $sql_encoding = "SELECT encoding_status, COUNT(*) as count FROM shipments GROUP BY encoding_status";
    $result_encoding = $conn->query($sql_encoding);

    if ($result_encoding) {
        while ($row = $result_encoding->fetch_assoc()) {
            if (array_key_exists($row['encoding_status'], $encoding_counts)) {
                $encoding_counts[$row['encoding_status']] = (int)$row['count'];
            }
        }
    }

    echo json_encode([
        'shipping_counts' => $shipping_counts,
        'encoding_counts' => $encoding_counts
    ]);
    $conn->close();
    exit;
}

if ($_GET['action'] === 'cd_counts') { 
    // Initialize counts for container deposit
    $cd_counts = [ 
        'CD' => 0,
        'CD COLLECTED' => 0,
        'NO CD' => 0
    ];

    // Query to get counts from the cd_status column in the shipments table (instead of shipment_db)
    $sql_cd = "SELECT cd_status, COUNT(*) as count FROM shipments GROUP BY cd_status";
    $result_cd = $conn->query($sql_cd);

    if ($result_cd) {
        while ($row = $result_cd->fetch_assoc()) {
            // Debugging: Log the row data
            error_log(print_r($row, true)); // This will help verify what we're getting back

            // Check if the cd_status exists in the array and update the count
            if (array_key_exists($row['cd_status'], $cd_counts)) {
                $cd_counts[$row['cd_status']] = (int)$row['count'];
            }
        }
    }

    // Send the response as JSON
    echo json_encode([
        'container_deposit' => $cd_counts
    ]);

    $conn->close();
    exit;
}

if ($action === 'fetch_details') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
        echo json_encode($shipment);
    } else {
        echo json_encode(["success" => false, "error" => "Shipment not found"]);
    }
    $stmt->close();
    exit;
}

if ($action === 'edit') {
    $id = intval($_GET['id']);
    $updates = json_decode(file_get_contents('php://input'), true);

    // Validate that protected fields are not being modified
    if (isset($updates['encoding_status']) || isset($updates['shipping_status']) || isset($updates['cd_status'])) {
        echo json_encode(["success" => false, "error" => "Cannot modify protected fields"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE shipments SET importer=?, commodity=?, num_containers=?, shipping_line=?, eta=?, bl_number=?, invoice_number=?, container_size=?, container_numbers=? WHERE id=?");
    $stmt->bind_param("ssissssssi",
        $updates['importer'],
        $updates['commodity'],
        $updates['num_containers'],
        $updates['shipping_line'],
        $updates['eta'],
        $updates['bl_number'],
        $updates['invoice_number'],
        $updates['container_size'],
        $updates['container_numbers'],
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    $stmt->close();
    exit;
}

if ($action === 'fetchDetails') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $shipment = $result->fetch_assoc();
        echo json_encode(["success" => true, "shipment" => $shipment]);
    } else {
        echo json_encode(["success" => false, "error" => "Shipment not found"]);
    }
}


$conn->close();
?>
