<?php
session_start();
include('../config/connection.php');
require_once('../config/email_utils.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to book an appointment']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    
    // Verify user exists in userdata table
    $verify_user = $conn->prepare("SELECT id FROM userdata WHERE id = ?");
    $verify_user->bind_param("i", $user_id);
    $verify_user->execute();
    $result = $verify_user->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user account']);
        exit;
    }

    // Get form data
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $service = $_POST['service'];
    $source = $_POST['source'];
    $appointment_date = $_POST['appointmentDate'];
    $appointment_time = $_POST['appointmentTime'];

    // Add this validation before processing the appointment
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;
    $current_datetime = date('Y-m-d H:i:s');

    if (strtotime($appointment_datetime) < strtotime($current_datetime)) {
        echo json_encode(['success' => false, 'message' => 'Cannot book appointments in the past']);
        exit;
    }

    // Check if the time slot is already booked
    $check_slot = $conn->prepare("
        SELECT id 
        FROM appointments 
        WHERE appointment_date = ? 
        AND appointment_time = ? 
        AND status != 'Cancelled'
    ");
    $check_slot->bind_param("ss", $appointment_date, $appointment_time);
    $check_slot->execute();
    $slot_result = $check_slot->get_result();

    if ($slot_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This time slot is already booked']);
        exit;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Insert appointment
        $stmt = $conn->prepare("INSERT INTO appointments (
            user_id, first_name, last_name, email, phone, 
            address, age, service, source, 
            appointment_date, appointment_time
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("isssssissss", 
            $user_id, $first_name, $last_name, $email, $phone, 
            $address, $age, $service, $source, 
            $appointment_date, $appointment_time
        );

        if ($stmt->execute()) {
            // Get the appointment ID
            $appointment_id = $conn->insert_id;

            // Get service price from services table
            $price_stmt = $conn->prepare("SELECT price FROM services WHERE service_name = ?");
            $price_stmt->bind_param("s", $service);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();
            $price = $price_result->fetch_assoc()['price'];

            // Format date and time for email
            $formatted_date = date('F d, Y', strtotime($appointment_date));
            $formatted_time = date('h:i A', strtotime($appointment_time));

            // Prepare appointment details for email
            $appointmentDetails = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'age' => $age,
                'service' => $service,
                'appointment_date' => $formatted_date,
                'appointment_time' => $formatted_time,
                'price' => $price
            ];

            // Send email notification to admin
            $emailSent = EmailUtils::sendAppointmentNotificationToAdmin($appointmentDetails);

            if ($emailSent) {
                $conn->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Appointment booked successfully and notification sent',
                    'appointment_id' => $appointment_id
                ]);
            } else {
                // If email fails, still commit the appointment but log the error
                error_log("Failed to send email notification for appointment ID: " . $appointment_id);
                $conn->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Appointment booked successfully but notification failed',
                    'appointment_id' => $appointment_id
                ]);
            }
        } else {
            throw new Exception("Error booking appointment");
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Appointment booking error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
    if (isset($price_stmt)) {
        $price_stmt->close();
    }
    $conn->close();
}
?>