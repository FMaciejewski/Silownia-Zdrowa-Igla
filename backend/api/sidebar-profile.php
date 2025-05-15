<?php
session_start();
$host = 'localhost';
$username = 'root'; 
$password = '';
$database = 'SilowniaZdrowaIgla';
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ID = $_SESSION['user_id'] ?? null;

$stmt = $conn->prepare("SELECT FirstName, LastName, ProfilePicture FROM users WHERE UserID = ?");
$stmt->bind_param("i", $ID);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
$stmt->close();
$conn->close();
?>