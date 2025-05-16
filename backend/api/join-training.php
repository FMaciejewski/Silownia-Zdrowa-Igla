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

$stmt = $conn->prepare("SELECT * FROM usertrainings WHERE UserID = ? AND TrainingID = ?");
$stmt->bind_param("ii", $ID, $TrainingID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header('Location: ../../frontend/sites/kalendarz.html?error=already_joined');
    exit();
}
$stmt->close();

$stmt = $conn->prepare("SELECT COALESCE(COUNT(usertrainings.UserID), 0) AS ParticipantCount, MaxParticipants FROM trainings LEFT JOIN usertrainings ON trainings.TrainingID = usertrainings.TrainingID WHERE trainings.TrainingID = ?");
$stmt->bind_param("i", $TrainingID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['ParticipantCount'] >= $row['MaxParticipants']) {
    header('Location: ../../frontend/sites/kalendarz.html?error=max_participants');
    exit();
}

$stmt = $conn->prepare("INSERT INTO usertrainings (UserID, TrainingID) VALUES (?, ?)");
$stmt->bind_param("ii", $ID, $TrainingID);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
    header('Location: ../../frontend/sites/kalendarz.html?success=6');
    exit();
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
