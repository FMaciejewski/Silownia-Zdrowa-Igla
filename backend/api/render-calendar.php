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

$stmt = $conn->prepare(
    "SELECT TrainingID, users.FirstName, users.LastName, Title, Description, StartTime, EndTime, MaxParticipants, Price, Location FROM trainings 
JOIN trainers ON trainings.TrainerID = trainers.TrainerID 
JOIN users ON trainers.UserID = users.UserID"
);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$stmt = $conn->prepare(
    "SELECT COUNT(usertrainings.TrainingID) as 'count', usertrainings.TrainingID FROM usertrainings 
JOIN trainings ON usertrainings.TrainingID = trainings.TrainingID
GROUP BY 2"
);
$stmt->execute();
$result = $stmt->get_result();
$participants = $result->fetch_all(MYSQLI_ASSOC);

$participantMap = [];
foreach ($participants as $p) {
    $participantMap[$p['TrainingID']] = $p['count'];
}

foreach ($events as &$event) {
    $id = $event['TrainingID'];
    $event['Participants'] = isset($participantMap[$id]) ? $participantMap[$id] : 0;
    $event["createdBy"] = $event['FirstName'] . " " . $event['LastName'];
}
$stmt->close();

$UserID = $_SESSION['user_id'] ?? null;
$stmt = $conn->prepare("SELECT Role FROM users WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();



$data = [
    'events' => $events,
    'role' => $role,
];

echo json_encode($data);

$stmt->close();
$conn->close();
