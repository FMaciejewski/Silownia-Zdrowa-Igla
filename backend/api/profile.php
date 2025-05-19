<?php

session_start();
header('Content-Type: application/json');

$db = new mysqli('localhost', 'root', '', 'silowniazdrowaigla');

if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Wystąpił błąd bazy."]);
    exit;
}

$id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE UserID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data['Role'] == 'trainer') {
    $stmt = $db->prepare("SELECT Specialization, Bio, HourlyRate FROM trainers WHERE UserID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $trainer = $result->fetch_assoc();
    $data['Specialization'] = $trainer['Specialization'];
    $data['Bio'] = $trainer['Bio'];
    $data['HourlyRate'] = $trainer['HourlyRate'];
}

echo json_encode($data);
