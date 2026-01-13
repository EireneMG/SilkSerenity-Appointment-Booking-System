<?php
session_start();
include('../config/connection.php');
require_once('email_utils.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.html');
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $appointment_id = $_GET['id'];
    $new_status = $_GET['status'];
    
    try {
        $conn->begin_transaction();
        
        // Update appointment status
        $update_stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $appointment_id);
        
        if ($update_stmt->execute()) {
            // Get appointment details for email
            $stmt = $conn->prepare("
                SELECT a.*, s.price 
                FROM appointments a 
                LEFT JOIN services s ON a.service = s.service_name 
                WHERE a.id = ?
            ");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $appointmentDetails = $result->fetch_assoc();
            
            // Send email notification to customer
            if (EmailUtils::sendStatusUpdateToCustomer($appointmentDetails, $new_status)) {
                $conn->commit();
                header('Location: admin_dashboard.php?success=Appointment status updated and notification sent');
            } else {
                throw new Exception("Failed to send email notification");
            }
        } else {
            throw new Exception("Failed to update appointment status");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: admin_dashboard.php?error=' . urlencode($e->getMessage()));
    }
    
    $update_stmt->close();
    $conn->close();
} else {
    header('Location: admin_dashboard.php?error=Invalid request');
}
?>