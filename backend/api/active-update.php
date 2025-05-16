<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

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

$currentDate = date('Y-m-d');

$sqlActivate = "UPDATE Passes SET IsActive = 1 
                WHERE PurchaseDate <= ? AND ExpiryDate >= ? AND IsActive = 0";
$stmtActivate = $conn->prepare($sqlActivate);
$stmtActivate->bind_param("ss", $currentDate, $currentDate);
$stmtActivate->execute();
$stmtActivate->close();

$sqlDeactivate = "UPDATE Passes SET IsActive = 0 
                  WHERE (PurchaseDate > ? OR ExpiryDate < ?) AND IsActive = 1";
$stmtDeactivate = $conn->prepare($sqlDeactivate);
$stmtDeactivate->bind_param("ss", $currentDate, $currentDate);
$stmtDeactivate->execute();
$stmtDeactivate->close();

$conn->close();
