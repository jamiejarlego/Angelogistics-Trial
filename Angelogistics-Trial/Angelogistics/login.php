<?php
session_start();
$response = array('success' => false, 'message' => '');

try {
    $conn = include('database/connection.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        error_log("Entered Username: " . $username);
        error_log("Entered Password: " . $password);

        if (empty($username) || empty($password)) {
            $response['message'] = "Please fill in both fields.";
            error_log("Error: " . $response['message']);
        } else {
            $sql = "SELECT User_ID, Username, Password FROM users WHERE LOWER(Username) = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $lower_username = strtolower($username);
                $stmt->bind_param("s", $lower_username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $stmt->close();

                    error_log("User found in database: Username = " . $user['Username']);
                    error_log("Stored Password: " . $user['Password']);

                    if ($password === $user['Password']) {
                        $_SESSION['user_id'] = $user['User_ID'];
                        $_SESSION['username'] = $user['Username'];
                        $response['success'] = true;
                        $response['message'] = "Login successful";
                        error_log("Login successful.");
                    } else {
                        $response['message'] = "Invalid username or password.";
                        error_log("Error: " . $response['message']);
                    }
                } else {
                    $response['message'] = "Invalid username or password.";
                    error_log("Error: User not found.");
                }
                $result->free();

            } else {
                $response['message'] = "Database error: " . $conn->error;
                error_log("Error: " . $response['message']);
            }
        }
    } else {
        $response['message'] = "Invalid request method.";
        error_log("Error: " . $response['message']);
    }
} catch (Exception $e) {
    $response['message'] = "An error occurred: " . $e->getMessage();
    error_log("Exception: " . $response['message']);
}

if ($conn) {
    $conn->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>