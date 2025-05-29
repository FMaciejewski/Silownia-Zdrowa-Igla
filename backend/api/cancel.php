<?php

session_start();

if (ob_get_level()) {
    ob_clean();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['type'])) {
        $missing = [];
        if (!isset($_SESSION['user_id'])) $missing[] = 'user_id';
        if (!isset($_GET['id'])) $missing[] = 'id';
        if (!isset($_GET['type'])) $missing[] = 'type';
        
        error_log("Brakuje danych: " . implode(', ', $missing));
        echo json_encode(['success' => false, 'message' => 'Brak wymaganych danych: ' . implode(', ', $missing)]);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $itemId = $_GET['id'];
    $type = $_GET['type'];

    error_log("userId: $userId, itemId: $itemId, type: $type");

    $mysqli = new mysqli('localhost', 'root', '', 'SilowniaZdrowaIgla');

    if ($mysqli->connect_error) {
        error_log("DB connection error: " . $mysqli->connect_error);
        echo json_encode(['success' => false, 'message' => 'Błąd połączenia z bazą: ' . $mysqli->connect_error]);
        exit;
    }

    error_log("DB connection OK");

    $stmt = $mysqli->prepare("SELECT role FROM Users WHERE UserID = ?");
    if (!$stmt) {
        error_log("Prepare error (user role): " . $mysqli->error);
        echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania: ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        error_log("User not found: $userId");
        echo json_encode(['success' => false, 'message' => 'Nie znaleziono użytkownika']);
        exit;
    }

    $role = $row['role'];
    error_log("User role: $role");

    if ($type === 'training') {
        if ($role === 'trainer') {
            error_log("Processing trainer training cancellation");
            
            $stmt2 = $mysqli->prepare("SELECT TrainerID FROM Trainers WHERE UserID = ?");
            if (!$stmt2) {
                error_log("Prepare error (trainer lookup): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania trainer: ' . $mysqli->error]);
                exit;
            }
            
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($row2 = $result2->fetch_assoc()) {
                $trainerId = $row2['TrainerID'];
                error_log("Found trainerId: $trainerId");
            } else {
                error_log("Trainer not found for userId: $userId");
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono trenera dla użytkownika']);
                $stmt2->close();
                exit;
            }
            $stmt2->close();

            $checkStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM Trainings WHERE TrainingID = ? AND TrainerID = ?");
            if (!$checkStmt) {
                error_log("Prepare error (training check): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd sprawdzania treningu: ' . $mysqli->error]);
                exit;
            }
            
            $checkStmt->bind_param("ii", $itemId, $trainerId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            if ($checkRow['count'] == 0) {
                error_log("Training not found or not owned by trainer. TrainingID: $itemId, TrainerID: $trainerId");
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono treningu lub brak uprawnień']);
                exit;
            }

            error_log("Training ownership verified");

            $stmt = $mysqli->prepare("DELETE FROM UserTrainings WHERE TrainingID = ?");
            if (!$stmt) {
                error_log("Prepare error (UserTrainings delete): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania UserTrainings: ' . $mysqli->error]);
                exit;
            }
            $stmt->bind_param("i", $itemId);
            
            if (!$stmt->execute()) {
                error_log("Execute error (UserTrainings delete): " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Błąd usuwania powiązań: ' . $stmt->error]);
                $stmt->close();
                exit;
            }
            
            $userTrainingsDeleted = $stmt->affected_rows;
            error_log("Deleted $userTrainingsDeleted rows from UserTrainings");
            $stmt->close();

            $stmt = $mysqli->prepare("DELETE FROM Trainings WHERE TrainingID = ? AND TrainerID = ?");
            if (!$stmt) {
                error_log("Prepare error (Trainings delete): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania Trainings: ' . $mysqli->error]);
                exit;
            }
            $stmt->bind_param("ii", $itemId, $trainerId);

        } elseif ($role === 'client') {
            error_log("Processing client training cancellation");
            
            $stmt = $mysqli->prepare("DELETE FROM UserTrainings WHERE TrainingID = ? AND UserID = ?");
            if (!$stmt) {
                error_log("Prepare error (client training): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania client training: ' . $mysqli->error]);
                exit;
            }
            $stmt->bind_param("ii", $itemId, $userId);
        } else {
            error_log("Invalid role for training: $role");
            echo json_encode(['success' => false, 'message' => 'Brak uprawnień - nieprawidłowa rola: ' . $role]);
            exit;
        }
        
    } elseif ($type === 'appointment') {
        if ($role === 'fizjo') {
            error_log("Processing fizjo appointment cancellation");
            
            $stmt2 = $mysqli->prepare("SELECT DoctorID FROM Doctors WHERE UserID = ?");
            if (!$stmt2) {
                error_log("Prepare error (doctor lookup): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania doctor: ' . $mysqli->error]);
                exit;
            }
            
            $stmt2->bind_param("i", $userId);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($row2 = $result2->fetch_assoc()) {
                $doctorId = $row2['DoctorID'];
                error_log("Found doctorId: $doctorId");
            } else {
                error_log("Doctor not found for userId: $userId");
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono lekarza']);
                $stmt2->close();
                exit;
            }
            $stmt2->close();

            $stmt = $mysqli->prepare("DELETE FROM UserAppointments WHERE AppointmentID = ? AND DoctorID = ?");
            if (!$stmt) {
                error_log("Prepare error (doctor appointment): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania doctor appointment: ' . $mysqli->error]);
                exit;
            }
            $stmt->bind_param("ii", $itemId, $doctorId);

        } elseif ($role === 'client' || $role === 'trainer') {
            error_log("Processing client appointment cancellation");
            
            $stmt = $mysqli->prepare("DELETE FROM UserAppointments WHERE AppointmentID = ? AND UserID = ?");
            if (!$stmt) {
                error_log("Prepare error (client appointment): " . $mysqli->error);
                echo json_encode(['success' => false, 'message' => 'Błąd przygotowania zapytania client appointment: ' . $mysqli->error]);
                exit;
            }
            $stmt->bind_param("ii", $itemId, $userId);
        } else {
            error_log("Invalid role for appointment: $role");
            echo json_encode(['success' => false, 'message' => 'Brak uprawnień - nieprawidłowa rola: ' . $role]);
            exit;
        }
    } else {
        error_log("Invalid type: $type");
        echo json_encode(['success' => false, 'message' => 'Nieprawidłowy typ: ' . $type]);
        exit;
    }

    error_log("About to execute final delete statement");
    
    if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        error_log("Delete executed successfully. Affected rows: $affectedRows");
        
        if ($affectedRows > 0) {
            error_log("SUCCESS - Item deleted");
            echo json_encode(['success' => true, 'message' => 'Pomyślnie anulowano']);
        } else {
            error_log("No rows affected - item not found or no permission");
            echo json_encode(['success' => false, 'message' => 'Nie znaleziono elementu do usunięcia lub brak uprawnień']);
        }
    } else {
        error_log("Execute error: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Błąd zapytania: ' . $stmt->error]);
    }

    $stmt->close();
    $mysqli->close();
    
    error_log("=== CANCEL.PHP DEBUG END ===");

} catch (Exception $e) {
    error_log("Exception caught: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Błąd serwera: ' . $e->getMessage()]);
}
?>