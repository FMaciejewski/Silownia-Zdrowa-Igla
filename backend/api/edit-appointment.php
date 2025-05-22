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

$appointmentID = $_POST['eventId'];
$doctorID = (int)$_POST['doctorId'];    
$cause = $_POST['cause'];
$startTimestamp = strtotime($_POST['start']);
$endTimestamp = $startTimestamp + 1800;
$startDate = date('Y-m-d H:i:s', $startTimestamp);
$endDate = date('Y-m-d H:i:s', $endTimestamp);

$stmt = $conn->prepare("SELECT WorkStartDate, WorkEndDate FROM doctors WHERE DoctorID = ?");
$stmt->bind_param("i", $doctorID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$workStartDate = $row['WorkStartDate'];  
$workEndDate = $row['WorkEndDate'];

$startTime = date('H:i:s', $startTimestamp);
$endTime = date('H:i:s', $endTimestamp);
$dayofweek = date('w', $startTimestamp);

function timeToMinutes($time) {
    list($hours, $minutes, $seconds) = explode(':', $time);
    return $hours * 60 + $minutes;
}

$workStartTime = timeToMinutes($workStartDate);
$workEndTime = timeToMinutes($workEndDate);
$startTime = timeToMinutes($startTime);
$endTime = timeToMinutes($endTime);
if ($startTime < $workStartTime || $endTime > $workEndTime || $dayofweek == 0 || $dayofweek == 6) {
    header('Location: ../../frontend/sites/appointments.html?error=wrong_time');
    exit();
}
$stmt->close();
$stmt = $conn->prepare("SELECT AppointmentID FROM userappointments WHERE StartDate = ? AND DoctorID = ? AND AppointmentID != ?");
$stmt->bind_param("sii", $startDate, $doctorID, $appointmentID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header('Location: ../../frontend/sites/appointments.html?error=wrong_time');
    exit();
}

$stmt = $conn->prepare("UPDATE userappointments SET Cause = ?, StartDate = ?, EndDate = ? WHERE AppointmentID = ?");
$stmt->bind_param("sssi", $cause, $startDate, $endDate, $appointmentID);
if ($stmt->execute()) {
    header('Location: ../../frontend/sites/appointments.html?success=7');
} else {
    header('Location: ../../frontend/sites/appointments.html?error=blad_sql');
}
?>