<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
header('Content-Type: application/json');
echo json_encode([
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
]);
