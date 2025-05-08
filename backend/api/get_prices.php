<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'silowniazdrowaigla';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Błąd połączenia: " . $conn->connect_error);
    }

    // Pobierz parametr okresu
    $period = $_GET['period'] ?? '';

    // Walidacja parametru
    if (!in_array($period, ['1', '3', '12'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nieprawidłowy okres. Dostępne wartości: 1, 3, 12']);
        exit;
    }

    // Pobierz wszystkie typy karnetów
    $result = $conn->query("SELECT type, month, three_months, year FROM pass_types");
    
    if ($result->num_rows === 0) {
        throw new Exception("Brak danych o karnetach w bazie danych");
    }

    $response = [];
    while ($row = $result->fetch_assoc()) {
        // Dopasuj cenę do wybranego okresu
        $price = match($period) {
            '1' => $row['month'],
            '3' => $row['three_months'],
            '12' => $row['year'],
            default => 0
        };
        
        // Dodaj do odpowiedzi używając typu karnetu jako klucza
        $response[strtolower($row['type'])] = $price;
    }

    $conn->close();

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>