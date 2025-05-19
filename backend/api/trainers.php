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
$stmt = $conn->prepare(
    "SELECT ProfilePicture, FirstName, LastName, Specialization, Bio, HourlyRate, PhoneNumber, Email FROM trainers
JOIN users ON trainers.UserID = users.UserID"
);
$stmt->execute();
$result = $stmt->get_result();
$trainers = array();
while ($row = $result->fetch_assoc()) {
    $trainers[] = array(
        'ProfilePicture' => $row['ProfilePicture'],
        'FirstName' => $row['FirstName'],
        'LastName' => $row['LastName'],
        'Specialization' => $row['Specialization'],
        'Bio' => $row['Bio'],
        'HourlyRate' => $row['HourlyRate'],
        'PhoneNumber' => $row['PhoneNumber'],
        'Email' => $row['Email']
    );
}

echo json_encode($trainers);
$stmt->close();
$conn->close();
