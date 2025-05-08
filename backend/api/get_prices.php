<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'silowniazdrowaigla';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

$duration = $_POST['duration'] ?? '';

if (empty($duration)) {
    die("Nieprawidłowe żądanie.");
}
switch ($duration) {
    case '1':
        $column = 'month';
        break;
    case '3':
        $column = 'three_months';
        break;
    case '12':
        $column = 'year';
        break;
    default:
        die("Nieprawidłowy okres.");
}

$stmt = $conn->prepare("SELECT price FROM prices WHERE duration = ?");
$stmt->bind_param("s", $column);
$stmt->execute();
$result = $stmt->get_result();
$prices = [];
while ($row = $result->fetch_assoc()) {
    $prices[] = $row['price'];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($prices);

?>