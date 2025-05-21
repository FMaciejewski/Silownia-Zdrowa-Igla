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

$doctorID = (int)$_GET['doctorId'];
$stmt = $conn->prepare("SELECT WorkStartDate, WorkEndDate FROM doctors WHERE DoctorID = ?");
$stmt->bind_param("i", $doctorID);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);
?>