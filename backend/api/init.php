<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd połączenia z bazą danych: ' . $conn->connect_error]);
    exit;
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbName");
$conn->select_db($dbName);




$tableCheck = $conn->query("SHOW TABLES LIKE 'Users'");
if ($tableCheck->num_rows === 0) {
    $sql = "
        CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Login VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    PhoneNumber VARCHAR(15),
    Role ENUM('client', 'trainer', 'admin', 'fizjo') DEFAULT 'client',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    LastLogin TIMESTAMP NULL
) ENGINE=InnoDB;
    ";
    if (!$conn->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd SQL(Users): ' . $conn->error]);
        exit;
    }

}




$tableCheck = $conn->query("SHOW TABLES LIKE 'Trainers'");
if ($tableCheck->num_rows === 0) {
    $sql = "
        CREATE TABLE Trainers (
    TrainerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL UNIQUE,
    Specialization VARCHAR(100) NOT NULL,
    Bio TEXT,
    HourlyRate DECIMAL(10,2),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;
    ";
    if (!$conn->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd SQL(Trainers): ' . $conn->error]);
        exit;
    }

}




$tableCheck = $conn->query("SHOW TABLES LIKE 'Passes'");
if ($tableCheck->num_rows === 0) {
    $sql = "
        CREATE TABLE Passes (
    PassID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Type ENUM('1-month', '3-months', '6-months', '12-months') NOT NULL,
    PurchaseDate DATETIME NOT NULL,
    ExpiryDate DATETIME NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;
    ";
    if (!$conn->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd SQL(Passes): ' . $conn->error]);
        exit;
    }

}




$tableCheck = $conn->query("SHOW TABLES LIKE 'Trainings'");
if ($tableCheck->num_rows === 0) {
    $sql = "
        CREATE TABLE Trainings (
    TrainingID INT AUTO_INCREMENT PRIMARY KEY,
    TrainerID INT NOT NULL,
    Title VARCHAR(100) NOT NULL,
    Description TEXT,
    StartTime DATETIME NOT NULL,
    EndTime DATETIME NOT NULL,
    MaxParticipants INT DEFAULT 10,
    Price DECIMAL(10,2) DEFAULT 0,
    Location VARCHAR(100),
    FOREIGN KEY (TrainerID) REFERENCES Trainers(TrainerID)
) ENGINE=InnoDB;
    ";
    if (!$conn->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd SQL(Trainings): ' . $conn->error]);
        exit;
    }

}




$tableCheck = $conn->query("SHOW TABLES LIKE 'UserTrainings'");
if ($tableCheck->num_rows === 0) {
    $sql = "
        CREATE TABLE UserTrainings (
    UserID INT NOT NULL,
    TrainingID INT NOT NULL,
    SignupDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    PRIMARY KEY (UserID, TrainingID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (TrainingID) REFERENCES Trainings(TrainingID) ON DELETE CASCADE
) ENGINE=InnoDB;
    ";
    if (!$conn->query($sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Błąd SQL(UserTrainings): ' . $conn->error]);
        exit;
    }

}



$tableCheck = $conn->query("SHOW TABLES LIKE 'Payments'");
if ($tableCheck->num_rows === 0) {
    $sql ="
        CREATE TABLE Payments (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod ENUM('card', 'transfer', 'cash') NOT NULL,
    Status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    PassID INT NULL,
    TrainingID INT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (PassID) REFERENCES Passes(PassID),
    FOREIGN KEY (TrainingID) REFERENCES Trainings(TrainingID)
) ENGINE=InnoDB;
    ";
if (!$conn->query($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Błąd SQL(Payments): ' . $conn->error]);
    exit;
}
}


do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
} while ($conn->more_results() && $conn->next_result());



echo json_encode(['success' => true, 'message' => 'Baza gotowa']);
exit;
?>
