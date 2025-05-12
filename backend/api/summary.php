<?php
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

$rodzaj = $_GET['rodzaj'] ?? '';
$okres = $_GET['okres'] ?? '';

if (empty($rodzaj) || empty($okres)) {
    http_response_code(400);
    echo json_encode(['error' => 'Brak wymaganych parametrów']);
    exit;
}

$column = match ($okres) {
    '1' => 'month',
    '3' => 'three_months',
    '12' => 'year',
    default => null
};

if (!$column) {
    http_response_code(400);
    echo json_encode(['error' => 'Nieprawidłowy okres']);
    exit;
}

$stmt = $conn->prepare("SELECT $column AS price FROM pass_types WHERE type = ?");
$stmt->bind_param("s", $rodzaj);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode(['price' => $data['price']]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Nie znaleziono ceny dla podanych parametrów']);
}

$stmt->close();
$conn->close();
?>