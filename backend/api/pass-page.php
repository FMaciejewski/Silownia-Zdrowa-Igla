<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych']);
    exit;
}


$UserID = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT FirstName, LastName, ProfilePicture FROM Users WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

if (!$userData) {
    http_response_code(404);
    echo json_encode(['error' => 'Użytkownik nie znaleziony']);
    exit;
}

$stmt = $conn->prepare("SELECT Type, PurchaseDate, ExpiryDate FROM Passes WHERE UserID = ? LIMIT 1");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$passResult = $stmt->get_result();
$passData = $passResult->fetch_assoc();
$stmt->close();

$conn->close();

$response = $userData;
$response['Pass'] = $passData ?: null;

echo json_encode($response);
