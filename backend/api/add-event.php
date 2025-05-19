<?php

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

$UserID = $_SESSION['user_id'] ?? null;
$stmt = $conn->prepare("SELECT TrainerID FROM Trainers WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$stmt->bind_result($eventCreatorId);
$stmt->fetch();
$stmt->close();

$eventName = $_POST['title'] ?? null;
$eventDescription = $_POST['description'] ?? null;
$maxParticipants = $_POST['maximum'] ?? null;
$eventPrice = $_POST['price'] ?? null;
$eventLocation = $_POST['location'] ?? null;
$eventStart = $_POST['eventStart'] ?? null;
$eventEnd = $_POST['eventEnd'] ?? null;

$stmt = $conn->prepare("INSERT INTO Trainings (TrainerID, Title, Description, StartTime, EndTime, MaxParticipants, Price, Location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssiss", $eventCreatorId, $eventName, $eventDescription, $eventStart, $eventEnd, $maxParticipants, $eventPrice, $eventLocation);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: ../../frontend/sites/kalendarz.html');
    exit;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd rejestracji: ' . $stmt->error]);
    header('Location: ../../frontend/sites/kalendarz.html?error=błąd_sql');
    exit;
}

$stmt->close();
$conn->close();
