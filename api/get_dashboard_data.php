<?php
session_start();
include('../config/connection.php');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // Get today's appointments
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $today_count = $stmt->get_result()->fetch_assoc()['count'];

    // Get total customers
    $customer_count = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM appointments")->fetch_assoc()['count'];

    // Get weekly revenue
    $week_start = date('Y-m-d', strtotime('-7 days'));
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE date_time >= ?");
    $stmt->bind_param("s", $week_start);
    $stmt->execute();
    $weekly_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'today_appointments' => $today_count,
            'total_customers' => $customer_count,
            'weekly_revenue' => $weekly_revenue
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>