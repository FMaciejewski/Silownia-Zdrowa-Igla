<?php
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
        $_SESSION['user_id'] = $user['id'];
        header("Zalogowano pomyślnie!", "../../frontend/sites/profile.html?success=1");
    } else {
        header("Niepoprawny login lub hasło.", "../../frontend/sites/log-in.html?error=wrong");
    }

    $stmt->close();
} else {
    die("Nieprawidłowe żądanie.");
}

$conn->close();

?>