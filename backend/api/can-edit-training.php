<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'SilowniaZdrowaIgla';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $conn->connect_error]);
    exit;
}

$ID = $_SESSION['user_id'];
$training_id = $_GET['trainingId'];

$stmrt = $conn->prepare("SELECT users.UserID FROM users
JOIN trainers ON users.UserID = trainers.UserID 
JOIN trainings ON trainers.TrainerID = trainings.TrainerID
WHERE TrainingID = ? AND users.UserID = ?");
$stmrt->bind_param("ii", $training_id, $ID);
$stmrt->execute();
$result = $stmrt->get_result();
if ($result->num_rows > 0) {
    $data = [
        'canEdit' => true,
    ];
} else {
    $data = [
        'canEdit' => false,
    ];
}
echo json_encode($data);

$stmrt->close();
$conn->close();
?>