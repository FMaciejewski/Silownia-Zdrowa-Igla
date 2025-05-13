<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'SilowniaZdrowaIgla';
$conn = mysqli_connect($host, $username, $password, $database);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT TrainingID, (SELECT COUNT(usertrainings.TrainingID) FROM usertrainings 
JOIN trainings ON usertrainings.TrainingID = trainings.TrainingID) as 'Participants', users.FirstName, users.LastName, Title, Description, StartTime, EndTime, MaxParticipants, Price, Location FROM trainings 
JOIN trainers ON trainings.TrainerID = trainers.TrainerID 
JOIN users ON trainers.UserID = users.UserID");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);

$stmt->close();
$conn->close();
?>