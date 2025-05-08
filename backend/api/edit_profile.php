<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'silowniazdrowaigla';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

$firstName = $_POST['first-name'] ?? '';
$lastName = $_POST['last-name'] ?? '';
$login = $_POST['login'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNumber = "+48" . $_POST['phone-number'] ?? '';
$profilePicture = $_FILES['profile-picture'] ?? null;

$ID = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT PRofilePicture FROM users WHERE UserID = ?");
$stmt->bind_param("i", $ID);
$stmt->execute();
$stmt->bind_result($currentProfilePicture);
$stmt->fetch();
$stmt->close();

if ($profilePicture && $profilePicture['error'] === 0) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileName = $profilePicture['name'];
    $fileTmpName = $profilePicture['tmp_name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        die("Niedozwolony format pliku.");
    }

    $newFileName = uniqid('profile_', false) . '.' . $fileExtension;
    $uploadDir = '../../frontend/assets/images/';
    $uploadPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpName, $uploadPath)) {
        die("Błąd podczas przesyłania zdjęcia.");
    }
    $profilePicturePath = $uploadPath;
} else {
    $profilePicturePath = null;
}

if ($profilePicturePath) {
    $relativePath = 'assets/images/' . basename($profilePicturePath);
    $query = "UPDATE users SET FirstName = ?, LastName = ?, Login = ?, Email = ?, PhoneNumber = ?, ProfilePicture = ? WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi",$firstName, $lastName, $login, $email, $phoneNumber, $relativePath, $ID);
    if(!$stmt->execute()) {
        die("Błąd podczas aktualizacji danych: " . $stmt->error);
    }
    if ($currentProfilePicture && $currentProfilePicture !== 'assets/images/default_profile.png') {
        $oldFilePath = '../../frontend/' . $currentProfilePicture;
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }
    $stmt->close();
} else {
    $query = "UPDATE users SET FirstName = ?, LastName = ?, Login = ?, Email = ?, PhoneNumber = ? WHERE UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi",$firstName, $lastName, $login, $email, $phoneNumber, $ID);
    if(!$stmt->execute()) {
        die("Błąd podczas aktualizacji danych: " . $stmt->error);
    }
    $stmt->close();
}

header('Location: ../../frontend/sites/profile.html');
exit();
?>
