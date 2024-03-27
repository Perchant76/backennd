<?php
header('Content-Type: application/json');

// Create connection
$conn = new mysqli('6api.perchant.dev', 'a258273_app1', 'Q76mkkaB', 'd258273_app1');
// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get token and station from request
$token = isset($_GET['token']) ? $_GET['token'] : '';
$station = isset($_GET['station']) ? $_GET['station'] : '';

if (empty($token) || empty($station)) {
    echo json_encode(['error' => 'Token and station are required']);
    exit;
}

// Check if user is on the list
$stmt = $conn->prepare("SELECT id, is_on_list FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!$row['is_on_list']) {
        // User is not on the list, increment passengers and update user status
        $conn->begin_transaction();
        try {
            // Increment passengers for the station
            $conn->query("UPDATE stations SET passengers = passengers + 1 WHERE station_name = '$station'");
            
            // Update user's is_on_list to true
            $update_stmt = $conn->prepare("UPDATE users SET is_on_list = TRUE WHERE id = ?");
            $update_stmt->bind_param("i", $row['id']);
            $update_stmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => 'Passenger count updated']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['error' => 'Transaction failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => 'User is already on the list']);
    }
} else {
    echo json_encode(['error' => 'Invalid token']);
}

$conn->close();
?>

