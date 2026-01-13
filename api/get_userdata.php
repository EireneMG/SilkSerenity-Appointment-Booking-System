<?php
session_start();
include('../config/connection.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // For debugging
    error_log("User ID: " . $user_id);
    
    $stmt = $conn->prepare("SELECT email, password FROM userdata WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // For debugging
        error_log("Found user data: " . json_encode($row));
        
        echo json_encode([
            'success' => true,
            'email' => $row['email'],
            'password' => $row['password']
        ]);
    } else {
        // For debugging
        error_log("No user found for ID: " . $user_id);
        
        echo json_encode([
            'success' => false, 
            'message' => 'User not found',
            'debug_id' => $user_id
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_user_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => true
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>