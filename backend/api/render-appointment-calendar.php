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
$ID = $_SESSION['user_id'];
$doctorID = $_GET['doctorId'];

$stmt = $conn->prepare("SELECT 
    ua.AppointmentID,
    ua.Cause AS title,
    ua.StartDate AS start,
    ua.EndDate AS end,
    CONCAT(d.Degree, ' ', u1.FirstName, ' ', u1.LastName) AS Doctor,
    CONCAT(u2.FirstName, ' ', u2.LastName) AS Patient
  FROM UserAppointments ua
  JOIN Doctors d ON ua.DoctorID = d.DoctorID
  JOIN Users u1 ON d.UserID = u1.UserID
  JOIN Users u2 ON ua.UserID = u2.UserID
  WHERE ua.DoctorID = ?");
$stmt->bind_param("i", $doctorID);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$data = [
    'events' => $events,
    'id' => $ID,
];

echo json_encode($data);
$conn->close();
?>