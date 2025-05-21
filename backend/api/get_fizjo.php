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
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'silowniazdrowaigla';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error']);
    exit;
}

// Get current user info
$currentUserId = $_SESSION['user_id'];
$currentUserName = $_SESSION['username'];

// Get receiver info
$query = "SELECT id, username FROM users WHERE id = ?";
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database prepare error: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('i', $receiverId);
$stmt->execute();
$result = $stmt->get_result();
$receiver = $result->fetch_assoc();

$stmt->close();
$mysqli->close();

if (!$receiver) {
    http_response_code(404);
    echo json_encode(['error' => 'Receiver not found']);
    exit;
}

echo json_encode([
    'currentUserId' => $currentUserId,
    'currentUserName' => $currentUserName,
    'receiverId' => $receiver['id'],
    'receiverName' => $receiver['username']
]);