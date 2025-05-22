<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$receiverId = filter_input(INPUT_GET, 'receiver_id', FILTER_VALIDATE_INT);
if (!$receiverId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid receiver ID']);
    exit;
}
$host = 'localhost';
$db   = 'SilowniaZdrowaIgla';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit;
}

$userId = $_SESSION['user_id'];
$messages = [];

$query = "
    SELECT m.*, CONCAT(u.FirstName, ' ', u.LastName) AS sender_name 
    FROM Messages m
    JOIN Users u ON m.sender_id = u.UserID
    WHERE (m.sender_id = ? AND m.receiver_id = ?)
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.sent_at ASC
";

$stmt = $mysqli->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('iiii', $userId, $receiverId, $receiverId, $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender_id' => $row['sender_id'],
        'sender_name' => $row['sender_name'],
        'message' => $row['message'],
    ];
}


$stmt->close();
$mysqli->close();

echo json_encode($messages);