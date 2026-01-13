<?php
session_start();
include('../config/connection.php');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "
        SELECT 
            a.*,
            u.username,
            u.email
        FROM appointments a
        JOIN userdata u ON a.user_id = u.id
        WHERE 1=1
    ";
    
    // Add filters
    if (!empty($data['date'])) {
        $query .= " AND DATE(a.appointment_date) = ?";
    }
    if (!empty($data['status'])) {
        $query .= " AND a.status = ?";
    }
    
    $query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters if filters exist
    if (!empty($data['date'])) {
        $stmt->bind_param("s", $data['date']);
    }
    if (!empty($data['status'])) {
        $stmt->bind_param("s", $data['status']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode(['success' => true, 'appointments' => $appointments]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>