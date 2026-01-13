<?php
session_start();
include('../config/connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to view appointments']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Join with userdata table to ensure user exists
    $query = "
        SELECT 
            a.id as appointment_id,
            a.service,
            a.appointment_date,
            a.appointment_time,
            a.status,
            u.username,
            u.email
        FROM appointments a
        INNER JOIN userdata u ON a.user_id = u.id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        // Format date and time for display
        $date = new DateTime($row['appointment_date']);
        $time = new DateTime($row['appointment_time']);
        
        $appointments[] = [
            'id' => $row['appointment_id'],
            'service' => $row['service'],
            'appointment_date' => $date->format('F j, Y'), // Format: January 1, 2024
            'appointment_time' => $time->format('g:i A'),  // Format: 9:00 AM
            'status' => $row['status']
        ];
    }

    echo json_encode(['success' => true, 'appointments' => $appointments]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching appointments']);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>