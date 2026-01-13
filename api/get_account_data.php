<?php
session_start();
require '../config/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the response is in JSON format
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details (email, password) from the database
$sql = "SELECT email, password FROM userdata WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Error preparing statement']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

// Check if the user exists
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $email = $user['email'];
    $password = $user['password']; // This could be hashed
} else {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Fetch user appointments
$query_appointments = "SELECT service, appointment_date, appointment_time, status FROM appointments WHERE user_id = ?";
$stmt_appointments = $conn->prepare($query_appointments);
$stmt_appointments->bind_param("i", $user_id);
$stmt_appointments->execute();
$result_appointments = $stmt_appointments->get_result();

// Store appointments in an array
$appointments = [];
while ($row = $result_appointments->fetch_assoc()) {
    $appointments[] = $row;
}


// Return the data as JSON
$response = [
    'email' => $email,
    'password' => $password,
    'appointments' => $appointments
];

echo json_encode($response);
exit; // Ensure no further output occurs

$stmt->close();
$appointments_stmt->close();
$conn->close();
?>
