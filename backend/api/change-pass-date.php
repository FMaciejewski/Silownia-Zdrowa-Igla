<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// Konfiguracja bazy danych
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Brak danych wejściowych');
    }

    $UserID = filter_var($data['UserID'] ?? null, FILTER_VALIDATE_INT);
    $newStartDate = $data['newStartDate'] ?? null;

    if (!$newStartDate) {
        throw new Exception('Brak wymaganych pól');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newStartDate)) {
        throw new Exception('Nieprawidłowy format daty. Użyj RRRR-MM-DD');
    }

    $conn = new mysqli($host, $user, $pass, $dbName);
    if ($conn->connect_error) {
        throw new Exception('Błąd połączenia z bazą danych');
    }
    $UserID = $_SESSION['user_id'];

    // Pobranie ostatniego karnetu
    $stmt = $conn->prepare("SELECT PassID, PurchaseDate, ExpiryDate FROM Passes WHERE UserID = ? ORDER BY PassID DESC LIMIT 1");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $pass = $result->fetch_assoc();
    $stmt->close();

    if (!$pass) {
        throw new Exception('Nie znaleziono aktywnego karnetu');
    }

    $passID = $pass['PassID'];
    $oldStart = new DateTime($pass['PurchaseDate']);
    $oldEnd = new DateTime($pass['ExpiryDate']);
    $duration = $oldStart->diff($oldEnd)->days;

    $newStart = new DateTime($newStartDate);
    $newEnd = clone $newStart;
    $newEnd->modify("+{$duration} days");

    $formattedStart = $newStart->format('Y-m-d H:i:s');
    $formattedEnd = $newEnd->format('Y-m-d H:i:s');

    // Aktualizacja karnetu
    $stmt = $conn->prepare("UPDATE Passes SET PurchaseDate = ?, ExpiryDate = ? WHERE PassID = ?");
    $stmt->bind_param("ssi", $formattedStart, $formattedEnd, $passID);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
