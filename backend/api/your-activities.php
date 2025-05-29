<?php
ob_start();
error_reporting(E_ALL);

session_start();

ob_clean();
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'SilowniaZdrowaIgla';


function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (empty($datetime)) return '';
    
    $date = new DateTime($datetime);
    return $date->format($format);
}


function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) return '';
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

try {
    $conn = new mysqli($host, $user, $pass, $dbName);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Brak autoryzacji', 'success' => false]);
        exit;
    }
    

    $stmt = $conn->prepare("SELECT role FROM Users WHERE UserID = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Użytkownik nie znaleziony', 'success' => false]);
        exit;
    }
    
    $role = $user['role'];
    $data = [];
    
    if ($role === 'client') {
        $data = [
            'trainings' => [],
            'appointments' => []
        ];
        

        $stmt = $conn->prepare("
            SELECT t.TrainingID, t.Title, t.StartTime, t.EndTime, t.Location,
                   CONCAT(u.FirstName, ' ', u.LastName) as TrainerName
            FROM UserTrainings ut
            JOIN Trainings t ON ut.TrainingID = t.TrainingID
            JOIN Trainers tr ON t.TrainerID = tr.TrainerID
            JOIN Users u ON tr.UserID = u.UserID
            WHERE ut.UserID = ? AND ut.Status != 'cancelled'
            ORDER BY t.StartTime ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trainingsData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        

        foreach ($trainingsData as $item) {
            $data['trainings'][] = [
                'TrainingID' => $item['TrainingID'],
                'Title' => $item['Title'],
                'TrainerName' => $item['TrainerName'],
                'StartTime' => $item['StartTime'],
                'EndTime' => $item['EndTime'],
                'Location' => $item['Location'],
                'StartTimeFormatted' => formatDateTime($item['StartTime']),
                'EndTimeFormatted' => formatDateTime($item['EndTime']),
                'DateOnly' => formatDate($item['StartTime']),
                'TimeRange' => formatDateTime($item['StartTime'], 'H:i') . ' - ' . formatDateTime($item['EndTime'], 'H:i'),
                'type' => 'training'
            ];
        }
        

        $stmt = $conn->prepare("
            SELECT a.AppointmentID, a.StartDate, a.EndDate, a.Cause,
                   CONCAT(u.FirstName, ' ', u.LastName) as DoctorName
            FROM UserAppointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u ON d.UserID = u.UserID
            WHERE a.UserID = ?
            ORDER BY a.StartDate ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointmentsData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        

        foreach ($appointmentsData as $item) {
            $data['appointments'][] = [
                'AppointmentID' => $item['AppointmentID'],
                'DoctorName' => $item['DoctorName'],
                'StartDate' => $item['StartDate'],
                'EndDate' => $item['EndDate'],
                'Cause' => $item['Cause'],
                'StartDateFormatted' => formatDateTime($item['StartDate']),
                'EndDateFormatted' => formatDateTime($item['EndDate']),
                'DateOnly' => formatDate($item['StartDate']),
                'TimeRange' => formatDateTime($item['StartDate'], 'H:i') . ' - ' . formatDateTime($item['EndDate'], 'H:i'),
                'type' => 'appointment'
            ];
        }
        
    } elseif ($role === 'trainer') {
        $stmt = $conn->prepare("SELECT TrainerID FROM Trainers WHERE UserID = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trainer = $result->fetch_assoc();
        $stmt->close();
        
        if (!$trainer) {
            echo json_encode([
                'success' => true,
                'role' => $role,
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $trainer_id = $trainer['TrainerID'];
        
        $stmt = $conn->prepare("
            SELECT t.TrainingID, t.Title, t.StartTime, t.EndTime, t.Location,
                   t.MaxParticipants,
                   (SELECT COUNT(*) FROM UserTrainings ut WHERE ut.TrainingID = t.TrainingID AND ut.Status != 'cancelled') as CurrentParticipants
            FROM Trainings t
            WHERE t.TrainerID = ?
            ORDER BY t.StartTime ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $trainer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rawData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        

        foreach ($rawData as $item) {
            $data[] = [
                'TrainingID' => $item['TrainingID'],
                'Title' => $item['Title'],
                'StartTime' => $item['StartTime'],
                'EndTime' => $item['EndTime'],
                'Location' => $item['Location'],
                'MaxParticipants' => $item['MaxParticipants'],
                'CurrentParticipants' => $item['CurrentParticipants'],
                'StartTimeFormatted' => formatDateTime($item['StartTime']),
                'EndTimeFormatted' => formatDateTime($item['EndTime']),
                'DateOnly' => formatDate($item['StartTime']),
                'TimeRange' => formatDateTime($item['StartTime'], 'H:i') . ' - ' . formatDateTime($item['EndTime'], 'H:i')
            ];
        }
        
    } elseif ($role === 'fizjo') {
        $stmt = $conn->prepare("SELECT DoctorID FROM Doctors WHERE UserID = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $doctor = $result->fetch_assoc();
        $stmt->close();
        
        if (!$doctor) {
            echo json_encode([
                'success' => true,
                'role' => $role,
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $doctor_id = $doctor['DoctorID'];
        
        $stmt = $conn->prepare("
            SELECT a.AppointmentID, u.FirstName, u.LastName, a.StartDate, a.EndDate, a.Cause
            FROM UserAppointments a
            JOIN Users u ON a.UserID = u.UserID
            WHERE a.DoctorID = ?
            ORDER BY a.StartDate ASC
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rawData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        foreach ($rawData as $item) {
            $data[] = [
                'AppointmentID' => $item['AppointmentID'],
                'FirstName' => $item['FirstName'],
                'LastName' => $item['LastName'],
                'FullName' => $item['FirstName'] . ' ' . $item['LastName'],
                'StartDate' => $item['StartDate'],
                'EndDate' => $item['EndDate'],
                'Cause' => $item['Cause'],
                'StartDateFormatted' => formatDateTime($item['StartDate']),
                'EndDateFormatted' => formatDateTime($item['EndDate']),
                'DateOnly' => formatDate($item['StartDate']),
                'TimeRange' => formatDateTime($item['StartDate'], 'H:i') . ' - ' . formatDateTime($item['EndDate'], 'H:i')
            ];
        }
        
    } elseif ($role === 'admin') {
        $data = [
            'trainings' => [],
            'appointments' => [],
            'users_count' => 0,
            'active_passes' => 0
        ];
        

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Users");
        $stmt->execute();
        $result = $stmt->get_result();
        $data['users_count'] = $result->fetch_assoc()['count'];
        $stmt->close();
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Passes WHERE IsActive = 1 AND ExpiryDate > NOW()");
        $stmt->execute();
        $result = $stmt->get_result();
        $data['active_passes'] = $result->fetch_assoc()['count'];
        $stmt->close();
        

        $stmt = $conn->prepare("
            SELECT t.TrainingID, t.Title, t.StartTime, t.EndTime, 
                   CONCAT(u.FirstName, ' ', u.LastName) as TrainerName
            FROM Trainings t
            JOIN Trainers tr ON t.TrainerID = tr.TrainerID
            JOIN Users u ON tr.UserID = u.UserID
            WHERE t.StartTime > NOW()
            ORDER BY t.StartTime ASC
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $trainings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        foreach ($trainings as $item) {
            $data['trainings'][] = [
                'TrainingID' => $item['TrainingID'],
                'Title' => $item['Title'],
                'TrainerName' => $item['TrainerName'],
                'StartTimeFormatted' => formatDateTime($item['StartTime']),
                'EndTimeFormatted' => formatDateTime($item['EndTime'])
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'role' => $role,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in your-activities.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Wystąpił błąd serwera',
        'success' => false
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>