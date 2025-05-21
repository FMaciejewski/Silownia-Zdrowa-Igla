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
$appointmentId = $_GET['appointmentId'];
$stmt = $conn->prepare("DELETE FROM userappointments WHERE AppointmentID = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
header('Location: ../../frontend/sites/appointments.html');
?>