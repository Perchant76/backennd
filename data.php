<?php
header('Content-Type: application/json');
$conn = new mysqli('6api.perchant.dev', 'a258273_app1', 'Q76mkkaB', 'd258273_app1');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$token = $_GET['token'];

$sql = "SELECT id FROM users WHERE token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $dataSql = "SELECT home, driver, `table` FROM data";
    $dataResult = $conn->query($dataSql);

    $data = [];
    while ($row = $dataResult->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized or invalid token']);
}

$conn->close();
?>

