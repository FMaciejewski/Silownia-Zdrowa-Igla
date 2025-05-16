<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $conn->connect_error]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT UserID FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
        echo json_encode(['valid' => true]);
    } else {
        echo json_encode(['valid' => false]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Nieprawidłowe żądanie']);
}
