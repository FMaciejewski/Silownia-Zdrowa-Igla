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


$currentUserId = $_SESSION['user_id'];

$stmtCurrent = $mysqli->prepare("SELECT FirstName, LastName FROM Users WHERE UserID = ?");
$stmtCurrent->bind_param('i', $currentUserId);
$stmtCurrent->execute();
$currentResult = $stmtCurrent->get_result();
$current = $currentResult->fetch_assoc();
$stmtCurrent->close();

if (!$current) {
    http_response_code(404);
    echo json_encode(['error' => 'Current user not found']);
    exit;
}

$currentUserName = $current['FirstName'] . ' ' . $current['LastName'];


$stmtReceiver = $mysqli->prepare("SELECT UserID, FirstName, LastName FROM Users WHERE UserID = ?");
$stmtReceiver->bind_param('i', $receiverId);
$stmtReceiver->execute();
$receiverResult = $stmtReceiver->get_result();
$receiver = $receiverResult->fetch_assoc();
$stmtReceiver->close();
$mysqli->close();

if (!$receiver) {
    http_response_code(404);
    echo json_encode(['error' => 'Receiver not found']);
    exit;
}

$receiverName = $receiver['FirstName'] . ' ' . $receiver['LastName'];


echo json_encode([
    'currentUserId' => $currentUserId,
    'currentUserName' => $currentUserName,
    'receiverId' => $receiver['UserID'],
    'receiverName' => $receiverName
]);
