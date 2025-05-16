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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? null;
    $confirmPassword = $_POST['confirmPassword'] ?? null;

    if (empty($token)) {
        die("Token jest wymagany.");
    }

    $stmt = $conn->prepare("SELECT UserID, login, email, firstName, lastName FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($password !== $confirmPassword) {
            header('Location: ../../frontend/sites/new-password.html?error=missmatch&token=' . $token);
            exit;
        }

        $null = null;
        $stmt = $conn->prepare("UPDATE Users SET Token = ?, LastLogin = NOW() WHERE UserID = ?");
        $stmt->bind_param("si", $null, $user['UserID']);
        $stmt->execute();
        $stmt->close();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET PasswordHash = ? WHERE UserID = ?");
        $stmt->bind_param("si", $hash, $user['UserID']);

        if ($stmt->execute()) {
            $email = $user['email'];
            $firstName = $user['firstName'];
            $lastName = $user['lastName'];
            $to = urlencode($email);
            $name = urlencode($firstName . ' ' . $lastName);
            $subject = urlencode('Zmienione hasło');
            $message = urlencode($firstName . ' ' . $lastName . ' Hasło do twojego konta zostało zmienione! Cieszymy się, że jesteś z nami.');

            $response = @file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$message");
            if ($response === false) {
                error_log("Nie udało się wysłać wiadomości e-mail.");
            }

            header('Location: ../../frontend/sites/log-in.html?success=2');
            exit;
        } else {
            http_response_code(500);
            die('Błąd rejestracji: ' . $stmt->error);
        }
    } else {
        die("Nieprawidłowy token.");
    }
} else {
    die("Nieprawidłowa metoda żądania.");
}

$conn->close();
