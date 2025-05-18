<?php
session_start();
include 'database/connection.php';

// Initialize the default profile image path
$defaultProfileImage = "sidebar-icon/profile-pic-icon.png";
$firstName = "Guest";
$middleName = ""; // Initialize middle name
$lastName = "";
$suffix = "";      // Initialize suffix
$username = "unknown";
$roleName = "N/A";
$profileImageSrc = $defaultProfileImage;
$userId = $_SESSION['user_id'] ?? 0;

if ($userId) {
    $sql = "SELECT u.First_Name, u.Middle_Name, u.Last_Name, u.Suffix, u.Username, u.profile_picture, r.Role_Name
            FROM users u
            JOIN roles r ON u.Role_ID = r.Role_ID
            WHERE u.User_ID = ?"; // Use a prepared statement here
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId); // "i" for integer
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set

    if ($result && $row = $result->fetch_assoc()) {
        $firstName = $row['First_Name'];
        $middleName = $row['Middle_Name']; // Fetch middle name
        $lastName = $row['Last_Name'];
        $suffix = $row['Suffix'];          // Fetch suffix
        $username = $row['Username'];
        $roleName = $row['Role_Name'];
        if (!empty($row['profile_picture'])) {
            $profileImageSrc = $row['profile_picture'];
        }
    }
    $stmt->close(); // Close the statement
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // --- Handle Save Profile ---
    if ($_POST['action'] === 'save_profile' && isset($_SESSION['user_id'])) {
        $userId = intval($_SESSION['user_id']);
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName'] ?? '');
        $middleName = mysqli_real_escape_string($conn, $_POST['middleName'] ?? ''); // Get middle name from POST
        $lastName = mysqli_real_escape_string($conn, $_POST['lastName'] ?? '');
        $suffix = mysqli_real_escape_string($conn, $_POST['suffix'] ?? '');          // Get suffix from POST
        $newUsername = mysqli_real_escape_string($conn, $_POST['username'] ?? '');

        $updateFields = [];

        if (!empty($firstName)) $updateFields[] = "First_Name = ?";
        if (!empty($middleName)) $updateFields[] = "Middle_Name = ?"; // Add middle name to update
        if (!empty($lastName)) $updateFields[] = "Last_Name = ?";
        if (!empty($suffix)) $updateFields[] = "Suffix = ?";            // Add suffix to update

        if (!empty($newUsername) && $newUsername !== $username) {
            $checkUsernameSql = "SELECT Username FROM users WHERE Username = ? AND User_ID != ?";
            $checkStmt = $conn->prepare($checkUsernameSql);
            $checkStmt->bind_param("si", $newUsername, $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose a different username.']);
                $checkStmt->close();
                exit();
            } else {
                $updateFields[] = "Username = ?";
            }
            $checkStmt->close();
        } elseif (!empty($newUsername)) {
            $updateFields[] = "Username = ?";
        }

        if (isset($_POST['profileImage']) && !empty($_POST['profileImage'])) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['profileImage']));
            $imageName = 'profile_' . $userId . '_' . time() . '.png';
            $imagePath = 'uploads/' . $imageName;

            if (!is_dir('uploads')) {
                if (!mkdir('uploads', 0755, true)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory.']);
                    exit();
                }
            }
            if (!file_put_contents($imagePath, $imageData)) {
                echo json_encode(['success' => false, 'message' => 'Failed to save profile image.']);
                exit();
            } else {
                $updateFields[] = "profile_picture = ?";
            }
        }

        if (!empty($updateFields)) {
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE User_ID = ?";
            $sqlTypes = str_repeat('s', count($updateFields)) . 'i'; // 's' for string, 'i' for integer
            $params = [];

            if (!empty($firstName)) $params[] = $firstName;
            if (!empty($middleName)) $params[] = $middleName;
            if (!empty($lastName)) $params[] = $lastName;
            if (!empty($suffix)) $params[] = $suffix;
            if (!empty($newUsername) && $newUsername !== $username) $params[] = $newUsername;
            elseif (!empty($newUsername)) $params[] = $newUsername;
            if (isset($_POST['profileImage']) && !empty($_POST['profileImage'])) $params[] = $imagePath;
            $params[] = $userId;

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($sqlTypes, ...$params); // Use ... to unpack the array
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => true, 'message' => 'Nothing to update.']);
        }
        exit();
    }

    // --- Handle Change Password ---
    if ($_POST['action'] === 'change_password' && isset($_SESSION['user_id'])) {
        $userId = intval($_SESSION['user_id']);
        $currentPassword = trim($_POST['currentPassword'] ?? '');
        $newPassword = trim($_POST['newPassword'] ?? '');

        $sql = "SELECT Password FROM users WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $storedPassword = trim($row['Password']);

            if ($currentPassword === $storedPassword) {
                $updateSql = "UPDATE users SET Password = ? WHERE User_ID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $newPassword, $userId); // Use the plain new password
                if ($updateStmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update password: ' . $updateStmt->error]);
                }
                $updateStmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching user data.']);
        }
        $stmt->close();
        exit();
    }

    // --- Handle Create Account ---
    if ($_POST['action'] === 'create_account') {
        $newFirstName = mysqli_real_escape_string($conn, $_POST['newFirstName'] ?? '');
        $newMiddleName = mysqli_real_escape_string($conn, $_POST['newMiddleName'] ?? ''); // Get middle name
        $newLastName = mysqli_real_escape_string($conn, $_POST['newLastName'] ?? '');
        $newSuffix = mysqli_real_escape_string($conn, $_POST['newSuffix'] ?? '');          // Get suffix
        $newUsername = mysqli_real_escape_string($conn, $_POST['newUsername'] ?? '');
        $newPassword = $_POST['newPassword'] ?? ''; // Get the plaintext password
        $newRole = mysqli_real_escape_string($conn, $_POST['newRole'] ?? '');

        $checkSql = "SELECT Username FROM users WHERE Username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $newUsername);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose a different username.']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();

        $roleId = null;
        if ($newRole === 'Admin') {
            $roleId = 'R001';
        } else if ($newRole === 'Employee') {
            $roleId = 'R002';
        }

        if ($roleId !== null) {
            $insertSql = "INSERT INTO users (First_Name, Middle_Name, Last_Name, Suffix, Username, Password, Role_ID)
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("sssssss", $newFirstName, $newMiddleName, $newLastName, $newSuffix, $newUsername, $newPassword, $roleId); // Use plain password
            if ($insertStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'New account created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error creating account: ' . $insertStmt->error]);
            }
            $insertStmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
        }
        exit();
    }
}

// Output the initial user data as JSON
$userData = [
    'firstName' => $firstName,
    'middleName' => $middleName,
    'lastName' => $lastName,
    'suffix' => $suffix,
    'username' => $username,
    'roleName' => $roleName,
    'profilePicture' => $profileImageSrc,
];

echo json_encode(['success' => true, 'userData' => $userData]);

$conn->close();
?>
