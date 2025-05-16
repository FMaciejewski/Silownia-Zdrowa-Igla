<?php

session_start();
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentPassword = $_POST['old-password'];
$newPassword = $_POST['new-password'];
$confirmPassword = $_POST['confirm-new-password'];

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT PasswordHash FROM users WHERE UserID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

if (password_verify($currentPassword, $hashedPassword)) {
    if ($newPassword !== $confirmPassword) {
        header('Location: ../../frontend/sites/profile.html?error=missmatch');
        exit;
    }
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET PasswordHash = ? WHERE UserID = ?");
    $stmt->bind_param("si", $newHashedPassword, $userId);
    if (!$stmt->execute()) {
        die("Error updating password: " . $stmt->error);
    }
} else {
    header('Location: ../../frontend/sites/profile.html?error=wrongpassword');
    exit;
}
