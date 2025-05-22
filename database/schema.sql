SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Users, Trainers, Passes, Trainings, UserTrainings, Payments;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabela użytkowników (rozszerzona wersja)
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
    LastLogin TIMESTAMP NULL,
    ProfilePicture VARCHAR(255) DEFAULT 'default_profile.png',
    Token VARCHAR(8) DEFAULT NULL;
    TokenCreatedAt TIMESTAMP NULL
) ENGINE=InnoDB;

-- Tabela trenerów (specjalna wersja użytkowników)
CREATE TABLE Trainers (
    TrainerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL UNIQUE,
    Specialization VARCHAR(100) NOT NULL,
    Bio TEXT,
    HourlyRate DECIMAL(10,2),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Doctors (
    DoctorID INT PRIMARY KEY AUTO_INCREMENT, 
    UserID INT NOT NULL,               
    Specialization VARCHAR(255),        
    Degree VARCHAR(100),              
    WorkStartDate TIME DEFAULT '08:00:00',
    WorkEndDate TIME DEFAULT '16:00:00',             
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela karnetów
CREATE TABLE Passes (
    PassID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Type ENUM('1-month', '3-months', '6-months', '12-months') NOT NULL,
    PurchaseDate DATETIME NOT NULL,
    ExpiryDate DATETIME NOT NULL,
    IsActive BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela treningów
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

-- Tabela zapisów
CREATE TABLE UserTrainings (
    UserID INT NOT NULL,
    TrainingID INT NOT NULL,
    SignupDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    PRIMARY KEY (UserID, TrainingID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (TrainingID) REFERENCES Trainings(TrainingID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE UserAppointments (
    AppointmentID INT AUTO_INCREMENT PRIMARY KEY,
  UserID INT NOT NULL,
  DoctorID INT NOT NULL,
  StartDate DATETIME NOT NULL,
  EndDate DATETIME NOT NULL,
  Cause VARCHAR(255) NOT NULL,
  FOREIGN KEY (UserID) REFERENCES Users(UserID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (DoctorID) REFERENCES Doctors(DoctorID)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabela płatności
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

--Tabela typu karnetu
CREATE TABLE pass_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE,
    month DECIMAL(10,2),
    three_months DECIMAL(10,2),
    year DECIMAL(10,2)
) ENGINE=InnoDB;

--Dane karnetów
INSERT INTO pass_types (type, month, three_months, year)
VALUES 
('Normal', 50, 130, 540),
('Premium', 150, 400, 1600),
('VIP', 220, 580, 2400);

--Tabela wiadomosci
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','delivered','read') NOT NULL DEFAULT 'sent',
  `sent_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `receiver_id` (`receiver_id`,`status`),
  KEY `sender_id` (`sender_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Indeksy dla optymalizacji
CREATE INDEX idx_user_email ON Users(Email);
CREATE INDEX idx_pass_expiry ON Passes(ExpiryDate, IsActive);
CREATE INDEX idx_training_dates ON Trainings(StartTime, EndTime);
CREATE INDEX idx_trainer_specialization ON Trainers(Specialization);