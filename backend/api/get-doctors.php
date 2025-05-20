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

$stmt = $conn->prepare("SELECT DoctorID, Degree, users.FirstName, users.LastName FROM doctors JOIN users ON doctors.UserID = users.UserID");
$stmt->execute();
$result = $stmt->get_result();
$doctors = array();
while ($row = $result->fetch_assoc()) {
    $doctors[] = array(
        'DoctorID' => $row['DoctorID'],
        'Degree' => $row['Degree'],
        'FirstName' => $row['FirstName'],
        'LastName' => $row['LastName']
    );
}

$stmt->close();
$conn->close();
echo json_encode($doctors);
?>