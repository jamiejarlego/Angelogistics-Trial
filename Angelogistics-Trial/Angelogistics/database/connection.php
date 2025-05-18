<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'acl-users';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo ""; // Add this line for debugging
}

return $conn;
?>