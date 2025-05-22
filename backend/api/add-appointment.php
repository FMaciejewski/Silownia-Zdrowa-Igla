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
$doctor_id = $_POST['doctorId'];
$cause = $_POST['cause'];
$start_time = $_POST['eventStart'];
$end_time = $_POST['eventEnd'];

$stmt = $conn->prepare("INSERT INTO userappointments (UserID, DoctorID, Cause, StartDate, EndDate) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $ID, $doctor_id, $cause, $start_time, $end_time);
if ($stmt->execute()) {
    header('Location: ../../frontend/sites/appointments.html?success=10');
    exit();
} else {
    header('Location: ../../frontend/sites/appointments.html?error=sqlerror');
    exit();
}
?>