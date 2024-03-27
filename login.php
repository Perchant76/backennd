<?php
header('Content-Type: application/json');
$conn = new mysqli('6api.perchant.dev', 'a258273_app1', 'Q76mkkaB', 'd258273_app1');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$username = $_POST['username'];
$password = $_POST['password']; // Assume this is the plain password sent by the user

$sql = "SELECT id, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        // Generate a secure token
        $token = bin2hex(random_bytes(64));
        
        // Save token in database
        $updateSql = "UPDATE users SET token = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $token, $row['id']);
        $updateStmt->execute();

        echo json_encode(['token' => $token]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

$conn->close();
?>

