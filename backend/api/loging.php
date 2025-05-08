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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT UserID, login, PasswordHash FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['PasswordHash'])) {
        $stmt = $conn->prepare("UPDATE Users SET LastLogin = NOW() WHERE UserID = ?");
        $stmt->bind_param("i", $user['UserID']);
        $stmt->execute();
        $stmt->close();
        $_SESSION['user_id'] = $user['UserID'];
        header("Location: ../../frontend/sites/profile.html?success=1");
        exit;
    } else {
        header("Location: ../../frontend/sites/log-in.html?error=wrong");
        exit;
    }

    $stmt->close();
} else {
    die("Nieprawidłowe żądanie.");
}

$conn->close();

?>