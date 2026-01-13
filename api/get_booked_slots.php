<?php
session_start();
include('../config/connection.php');

if (!isset($_GET['date'])) {
    echo json_encode(['error' => 'Date parameter is required']);
    exit;
}

$date = $_GET['date'];

try {
    // Get all booked time slots for the given date that are not cancelled
    $stmt = $conn->prepare("
        SELECT TIME_FORMAT(appointment_time, '%H:%i') as appointment_time
        FROM appointments 
        WHERE appointment_date = ? 
        AND status != 'Cancelled'
    ");
    
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookedSlots = [];
    while ($row = $result->fetch_assoc()) {
        $bookedSlots[] = $row['appointment_time'];
    }
    
    echo json_encode($bookedSlots);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching booked slots']);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>