<?php
session_start();
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'SilowniaZdrowaIgla';
$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$ID = $_SESSION['user_id'];
$appointmentId = $_GET['appointmentId'];
$stmt = $conn->prepare("SELECT * FROM userappointments WHERE AppointmentID = ? AND UserID = ?");
$stmt->bind_param("ii", $appointmentId, $ID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
        echo json_encode(array("canEdit" => true));
} else {
    echo json_encode(array("canEdit" => false));
}
?>