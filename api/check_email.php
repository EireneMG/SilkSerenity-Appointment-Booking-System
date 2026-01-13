<?php
include('../config/connection.php');

header('Content-Type: text/plain');

if (isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!$email) {
        echo 'error';
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];

        echo ($count > 0) ? 'exists' : 'not_exists';
        
        $stmt->close();
    } catch (Exception $e) {
        error_log('Email check error: ' . $e->getMessage());
        echo 'error';
    }
} else {
    echo 'error';
}

$conn->close();
?>