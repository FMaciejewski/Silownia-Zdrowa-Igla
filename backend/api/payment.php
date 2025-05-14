<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include 'active_update.php';


$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $user, $pass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $conn->connect_error]);
    exit;
}

$type = $_POST['rodzaj'] ?? null;
$PurchaseDate = $_POST['data_rozpoczecia'] ?? null;
$period = $_POST['okres'] ?? null;
$UserID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$UserID) {
    http_response_code(400);
    echo json_encode(['error' => 'Nieprawidłowy identyfikator użytkownika.']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM Passes WHERE UserID = ? AND Type = ?");
$stmt->bind_param("ss", $UserID, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentDate = date('Y-m-d');

if ($row['IsActive'] == 0 && $row['ExpiryDate'] < $currentDate) {
    $stmt = $conn->prepare("DELETE FROM Passes WHERE UserID = ? AND Type = ?");
    $stmt->bind_param("ss", $UserID, $type);
    
    if ($stmt->execute()) {
        error_log("Usunięto nieaktywny i przeterminowany karnet użytkownika $UserID o typie $type.");
    } else {
        error_log("Błąd przy usuwaniu karnetu: " . $stmt->error);
    }
    }else{
        $currentExpiryDate = $row['ExpiryDate'];
        $expiryDateObj = new DateTime($currentExpiryDate);
        $expiryDateObj->modify("+$period months");
        $newExpiryDate = $expiryDateObj->format('Y-m-d');

        $stmt = $conn->prepare("UPDATE Passes SET ExpiryDate = ? WHERE UserID = ? AND Type = ?");
        $stmt->bind_param("sss", $newExpiryDate, $UserID, $type);

        if ($stmt->execute()) {
            sendConfirmationEmail($conn, $UserID, $type, $PurchaseDate, $newExpiryDate, true);
            header('Location: ../../frontend/sites/profile.html?success=5');
            exit;
    }}
}   
$isActive = 1;
if (new DateTime($PurchaseDate) > new DateTime()) {
    $isActive = 0;
}
    $purchaseDateObj = new DateTime($PurchaseDate);
    $purchaseDateObj->modify("+$period months");
    $ExpiryDate = $purchaseDateObj->format('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO Passes (UserID, Type, PurchaseDate, ExpiryDate, IsActive) VALUES (?, ?, ?, ?,?)");
    $stmt->bind_param("sssss", $UserID, $type, $PurchaseDate, $ExpiryDate, $isActive);

    if ($stmt->execute()) {
        sendConfirmationEmail($conn, $UserID, $type, $PurchaseDate, $ExpiryDate, false);
        header('Location: ../../frontend/sites/profile.html?success=5');
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd podczas dodawania karnetu: ' . $stmt->error]);
    }


function sendConfirmationEmail($conn, $UserID, $type, $PurchaseDate, $ExpiryDate, $isRenewal) {
    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $firstName = $user['FirstName'];
    $lastName = $user['LastName'];
    $email = $user['Email'];
    
    $action = $isRenewal ? 'przedłużony' : 'zakupiony';
    $message = $firstName . ' ' . $lastName . ' Karnet został ' . $action . '. Rodzaj: ' . $type . 
               ' Data zakupu: ' . $PurchaseDate . ' Data ważności: ' . $ExpiryDate . 
               ' Zapraszamy do korzystania z naszych usług.';

    $to = urlencode($email);
    $name = urlencode($firstName . ' ' . $lastName);
    $subject = urlencode('Potwierdzenie ' . ($isRenewal ? 'przedłużenia' : 'zakupu'));
    $body = urlencode($message);

    file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$body");
}
?>