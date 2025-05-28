<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Połączenie z bazą (PDO)
$host = 'localhost';
$db   = 'SilowniaZdrowaIgla';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $e->getMessage()]);
    exit;
}

$stmt = $pdo->query("SELECT Users.UserID AS id, FirstName AS name, LastName AS surname, DoctorID FROM Users
JOIN Doctors ON Users.UserID = Doctors.UserID WHERE Role = 'fizjo'");
$physiotherapists = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($physiotherapists);
?>