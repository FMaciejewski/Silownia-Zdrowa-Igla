<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    header('Location: ../../frontend/sites/register.html?error=database');
    exit;
}

$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$email = $_POST['email'] ?? null;
$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;
$confirmPassword = $_POST['confirmPassword'] ?? null;
$phone = $_POST['tel'] ?? null;
$role = $_POST['role'] ?? 'client';

$phone = "+48" . $phone;

if ($password !== $confirmPassword) {
    header('Location: ../../frontend/sites/sign-in.html?error=missmatch');
    exit;
}

if (!$firstName || !$lastName || !$email || !$login || !$password || !$confirmPassword) {
    header('Location: ../../frontend/sites/sign-in.html?error=required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../../frontend/sites/sign-in.html?error=email');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM Users WHERE Login = ? OR Email = ?");
$stmt->bind_param("ss", $login, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header('Location: ../../frontend/sites/sign-in.html?error=already_registered');
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO Users (Login, PasswordHash, FirstName, LastName, Email, PhoneNumber, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $login, $hash, $firstName, $lastName, $email, $phone, $role);

if ($stmt->execute()) {
    $lastID = $conn->insert_id;

    if ($role === 'trainer') {
        $specialization = $_POST['specialization'] ?? null;
        $bio = $_POST['bio'] ?? null;
        $hourlyRate = $_POST['hourlyRate'] ?? null;

        $stmtTrain = $conn->prepare("INSERT INTO Trainers (UserID, Specialization, Bio, HourlyRate) VALUES (?, ?, ?, ?)");
        $stmtTrain->bind_param("issd", $lastID, $specialization, $bio, $hourlyRate);
        if (!$stmtTrain->execute()) {
            header('Location: ../../frontend/sites/sign-in.html?error=sql');
            exit;
        }
        $stmtTrain->close();
    } elseif ($role === 'fizjo') {
        $doc_specialization = $_POST['specialization-doc'] ?? null;
        $degree = $_POST['degree'] ?? null;
        $workStart = $_POST['work-start'] ?? null;
        $workEnd = $_POST['work-end'] ?? null;

        $stmtPhysio = $conn->prepare("INSERT INTO Doctors (UserID, Specialization, Degree, WorkStartDate, WorkEndDate) VALUES (?, ?, ?, ?, ?)");
        $stmtPhysio->bind_param("issss", $lastID, $doc_specialization, $degree, $workStart, $workEnd);
        if (!$stmtPhysio->execute()) {
            header('Location: ../../frontend/sites/sign-in.html?error=sql');
            exit;
        }
        $stmtPhysio->close();
    }


    $to = urlencode($email);
    $name = urlencode($firstName . ' ' . $lastName);
    $subject = urlencode('Witamy na naszej stronie');
    $message = urlencode($firstName . ' ' . $lastName . ' Dziękujemy za rejestrację! Cieszymy się, że jesteś z nami.');
    file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$message");

    header('Location: ../../frontend/sites/log-in.html?success=1');
    exit;
} else {
    header('Location: ../../frontend/sites/sign-in.html?error=sql');
    exit;
}

$stmt->close();
$conn->close();
