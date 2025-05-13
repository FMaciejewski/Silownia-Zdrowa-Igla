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

$date = date('Y-m-d');

// Pobierz wszystkie karnety, których data ważności minęła
$stmt = $conn->prepare("SELECT PassID, ExpiryDate FROM Passes WHERE ExpiryDate < ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
$expiredPasses = [];
while ($row = $result->fetch_assoc()) {
    $expiredPasses[] = $row;
}
$stmt->close();

// Zaktualizuj status karnetów, których data ważności minęła
$stmt = $conn->prepare("UPDATE Passes SET IsActive = '0' WHERE ExpiryDate < ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->close();

$conn->close();

// Zwróć listę zaktualizowanych karnetów
echo json_encode(['success' => true, 'expiredPasses' => $expiredPasses]);

?>