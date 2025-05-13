<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'SilowniaZdrowaIgla';
$conn = new mysqli($host, $username, $password, $dbname);

$ID = $_SESSION['user_id'];
$TrainingID = $_GET['trainingId'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO usertrainings (UserID, TrainingID) VALUES (?, ?)");
$stmt->bind_param("ii", $ID, $TrainingID);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
    header('Location: ../../frontend/sites/kalendarz.html');
    exit();
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>