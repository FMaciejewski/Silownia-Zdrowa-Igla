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

$training_id = $_POST['eventId'];
$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$location = $_POST['location'];
$maxParticipants = $_POST['maximum'];
$startDate = $_POST['start'];
$endDate = $_POST['end'];

if($startDate >= $endDate || $startDate < date('Y-m-d H:i:s')) {
    header('Location: ../../frontend/sites/kalendarz.html?error=invalid_date');
    exit();
}

$stmt = $conn->prepare("UPDATE trainings SET Title = ?, Description = ?, Price = ?, Location = ?, MaxParticipants = ?, StartTime = ?, EndTime = ? WHERE TrainingID = ?");
$stmt->bind_param("ssissssi", $title, $description, $price, $location, $maxParticipants, $startDate, $endDate, $training_id);
if ($stmt->execute()) {
    $stmt->close();
    $stmt = $conn->prepare("SELECT COALESCE(COUNT(usertrainings.UserID), 0) AS ParticipantCount FROM usertrainings LEFT JOIN trainings ON trainings.TrainingID = usertrainings.TrainingID WHERE usertrainings.TrainingID = ?");
    $stmt->bind_param("i", $training_id);
    $stmt->execute();
    $stmt->bind_result($participantCount);
    $stmt->fetch();
    $stmt->close();
    if ($participantCount > $maxParticipants) {
        $stmt = $conn->prepare("DELETE FROM usertrainings WHERE TrainingID = ? ORDER BY SignupDate DESC LIMIT ?");
        $limit = $participantCount - $maxParticipants;
        $stmt->bind_param("ii", $training_id, $limit);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: ../../frontend/sites/kalendarz.html?success=training_updated');
} else {
    header('Location: ../../frontend/sites/kalendarz.html?error=błąd_sql');
}
?>