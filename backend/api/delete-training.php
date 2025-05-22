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

$training_id = $_GET['trainingId'];

$stmt = $conn->prepare("DELETE FROM trainings WHERE TrainingID = ?");
$stmt->bind_param("i", $training_id);
$stmt->execute();
header('Location: ../../frontend/sites/kalendarz.html?success=8');

$stmt->close();
$conn->close();
