<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('../config/connection.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Get the review data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check for JSON errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Log the incoming data for debugging
error_log(print_r($data, true)); // Log the data to the server's error log

$appointment_id = isset($data['appointment_id']) ? intval($data['appointment_id']) : null;
$rating = isset($data['rating']) ? intval($data['rating']) : null;
$review_text = isset($data['review_text']) ? $data['review_text'] : null;

// Check if appointment_id is provided
if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID is required']);
    exit();
}

// Check if the user has already reviewed this appointment
$stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND appointment_id = ?");
$stmt->bind_param("ii", $user_id, $appointment_id);
$stmt->execute();
$stmt->bind_result($review_count);
$stmt->fetch();
$stmt->close();

if ($review_count > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this appointment.']);
    exit();
}

// Prepare and execute the insert statement
$stmt = $conn->prepare("INSERT INTO reviews (user_id, appointment_id, rating, review_text) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $user_id, $appointment_id, $rating, $review_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to post review: ' . $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>