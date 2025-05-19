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

$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$email = $_POST['email'] ?? null;
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;
$confirmPassword = $_POST['confirmPassword'] ?? null;
$phone = $_POST['tel'] ?? null;
$role = $_POST['role'] ?? 'client';
if ($role !== 'client') {
}

$phone = "+48" . $phone;
if ($password !== $confirmPassword) {
    header('Location: ../../frontend/sites/sign-in.html?error=missmatch');
    exit;
}
if (!$firstName || !$lastName || !$email || !$login || !$password || !$confirmPassword) {
    http_response_code(400);
    echo json_encode(['error' => 'Wszystkie pola są wymagane']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nieprawidłowy adres e-mail']);
    exit;
}
$stmt = $conn->prepare("SELECT * FROM Users WHERE Login = ? OR Email = ?");
$stmt->bind_param("ss", $login, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Login lub email już istnieje']);
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO Users (Login, PasswordHash, FirstName, LastName, Email, PhoneNumber, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $login, $hash, $firstName, $lastName, $email, $phone, $role);

if ($stmt->execute()) {
    if ($role !== 'client') {
        $specialization = $_POST['specialization'] ?? null;
        $bio = $_POST['bio'] ?? null;
        $hourlyRate = $_POST['hourlyRate'] ?? null;
        $lastID = $conn->insert_id;
        $stmtTrain = $conn->prepare("INSERT INTO Trainers (UserID, Specialization, Bio, HourlyRate) VALUES (?, ?, ?, ?)");
        $stmtTrain->bind_param("issd", $lastID, $specialization, $bio, $hourlyRate);
        if (!$stmtTrain->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd rejestracji trenera: ' . $stmtTrain->error]);
            header('Location: ../../frontend/sites/register.html?error=błąd_sql');
            exit;
        }
        $stmtTrain->close();
    }

    echo json_encode(['success' => true, 'message' => 'Rejestracja zakończona sukcesem']);
    $to = urlencode($email);
    $name = urlencode($firstName . ' ' . $lastName);
    $subject = urlencode('Witamy na naszej stronie');
    $message = urlencode($firstName . ' ' . $lastName . ' Dziękujemy za rejestrację! Cieszymy się, że jesteś z nami.');

    file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$message");


    header('Location: ../../frontend/sites/log-in.html?success=1');
    exit;
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd rejestracji: ' . $stmt->error]);
    header('Location: ../../frontend/sites/register.html?error=błąd_sql');
    exit;
}

    $stmt->close();
    $conn->close();
