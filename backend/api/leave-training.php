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

$ID = $_SESSION['user_id'];
$TrainingID = $_GET['trainingId'];

$stmt = $conn->prepare("DELETE FROM usertrainings WHERE UserID = ? AND TrainingID = ?");
$stmt->bind_param("ii", $ID, $TrainingID);
if ($stmt->execute()) {
    header('Location: ../../frontend/sites/kalendarz.html');
    exit();
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}
