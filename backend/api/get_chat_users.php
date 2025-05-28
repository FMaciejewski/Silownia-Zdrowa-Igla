<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$fizjoId = $_SESSION['user_id'];

$mysqli = new mysqli('localhost', 'root', '', 'SilowniaZdrowaIgla');
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$query = "
    SELECT DISTINCT u.UserID, u.FirstName, u.LastName
FROM Users u
JOIN Messages m ON (
    (u.UserID = m.sender_id AND m.receiver_id = ?)
    OR 
    (u.UserID = m.receiver_id AND m.sender_id = ?)
)
WHERE u.UserID != ?
ORDER BY u.FirstName;

";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('iii', $fizjoId, $fizjoId, $fizjoId);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode($users);
