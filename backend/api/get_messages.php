<?php
require_once __DIR__ . '/../../config.php';

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
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit;
}

$userId = $_SESSION['user_id'];
$messages = [];

$query = "
    SELECT m.*, u.username as sender_name 
    FROM messages m
    JOIN users u ON m.sender_id = u.id
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
    $messages[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode($messages);