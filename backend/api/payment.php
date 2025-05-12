<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
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
$UserID = $_SESSION['user_id'] ?? null;

$purchaseDateObj = new DateTime($PurchaseDate);
$purchaseDateObj->modify("+$period months");
$ExpiryDate = $purchaseDateObj->format('Y-m-d');



$stmt = $conn->prepare("INSERT INTO Passes (UserID, Type, PurchaseDate, ExpiryDate) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $UserID, $type, $PurchaseDate, $ExpiryDate);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Karnet został pomyślnie dodany.']);
    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = ?");
    $stmt->bind_param("s", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $firstName = $user['FirstName'];
    $lastName = $user['LastName'];
    $email = $user['Email'];
    $stmt->close();
    $to = urlencode($email);
    $name = urlencode($firstName . ' ' . $lastName);
    $subject = urlencode('Potwierdzenie zakupu');
    $message = urlencode($firstName . ' ' . $lastName.' Karnet został zakupiony. Rodzaj: '.$type.' Data zakupu: '.$PurchaseDate.' Data ważności: '.$ExpiryDate.' Zapraszamy do korzystania z naszych usług.');

    file_get_contents("http://localhost/Silownia-Zdrowa-Igla/backend/api/mail.php?to=$to&name=$name&subject=$subject&body=$message");
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd podczas dodawania karnetu: ' . $stmt->error]);
}

?>

$sesja = $_SESSION['user_id'] ?? null;