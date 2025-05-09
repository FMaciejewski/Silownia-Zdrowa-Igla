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

function generateToken($length = 8) {
    return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, $length);
}


if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['username'] ?? '';

    $stmt = $conn->prepare("SELECT UserID, login, email, firstName, lastName FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $token = generateToken();
        $stmt = $conn->prepare("UPDATE Users SET Token = ?, LastLogin = NOW() WHERE UserID = ?");
        $stmt->bind_param("si", $token, $user['UserID']);
        $stmt->execute();
        $stmt->close();

        $to = urlencode($user['email']);
        $name = urlencode($user['firstName'] . ' ' . $user['lastName']);
        $subject = urlencode('Zmiana hasła');
        $message =urlencode($user['firstName'] . ' ' . $user['lastName']. 'Aby zmienić hasło <a href="http://localhost/Silownia-Zdrowa-Igla/frontend/sites/new-password.html?token=' . $token . '">kliknij tutaj</a>');

        file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$message");

        $_SESSION['user_id'] = $user['UserID'];
        header("Location: ../../frontend/sites/pass-forget.html?success=3");
        exit;
    } else {
        header("Location: ../../frontend/sites/pass-forget.html?error=wronglogin");
        exit;
    }

    $stmt->close();
} else {
    die("Nieprawidłowe żądanie.");
}

$conn->close();

?>