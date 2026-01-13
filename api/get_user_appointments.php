<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized');
}

include('../config/connection.php');

if (isset($_GET['user_id'])) {
    // Get user info
    $stmt = $conn->prepare("
        SELECT 
            username, 
            email,
            created_at,
            (SELECT COUNT(*) FROM appointments WHERE user_id = userdata.id) as total_appointments,
            (SELECT COALESCE(SUM(amount), 0) FROM transactions t 
             JOIN appointments a ON t.appointment_id = a.id 
             WHERE a.user_id = userdata.id) as total_spent
        FROM userdata 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Display user info with statistics
    echo "<div class='user-info mb-4'>";
    echo "<h5>User Information</h5>";
    echo "<div class='row'>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
    echo "</div>";
    echo "<div class='col-md-6'>";
    echo "<p><strong>Member Since:</strong> " . date('M d, Y', strtotime($user['created_at'])) . "</p>";
    echo "<p><strong>Total Appointments:</strong> " . $user['total_appointments'] . "</p>";
    echo "<p><strong>Total Spent:</strong> ₱" . number_format($user['total_spent'], 2) . "</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    // Get all appointments for this user
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            t.payment_status,
            t.amount,
            t.payment_method,
            t.transaction_date
        FROM appointments a
        LEFT JOIN transactions t ON a.id = t.appointment_id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-hover'>
                <thead>
                    <tr>
                        <th>Appointment Date</th>
                        <th>Time</th>
                        <th>Name Used</th>
                        <th>Service</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th>Payment Info</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            $statusClass = 
                $row['status'] == 'Confirmed' ? 'success' : 
                ($row['status'] == 'Pending' ? 'warning' : 'danger');

            echo "<tr>";
            echo "<td>" . date('M d, Y', strtotime($row['appointment_date'])) . "</td>";
            echo "<td>" . date('h:i A', strtotime($row['appointment_time'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['service']) . "</td>";
            echo "<td>Phone: " . htmlspecialchars($row['phone']) . "<br>Email: " . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['age']) . "</td>";
            echo "<td><span class='badge bg-{$statusClass}'>{$row['status']}</span></td>";
            echo "<td>";
            if ($row['payment_status']) {
                echo "Status: " . $row['payment_status'] . "<br>";
                echo "Method: " . ($row['payment_method'] ?? 'N/A') . "<br>";
                if ($row['transaction_date']) {
                    echo "Date: " . date('M d, Y', strtotime($row['transaction_date']));
                }
            } else {
                echo "Pending Payment";
            }
            echo "</td>";
            echo "<td>₱" . number_format($row['amount'] ?? 0, 2) . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-info'>No appointments found for this user.</div>";
    }
}
?>

<style>
.user-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #734432;
}

.user-info h5 {
    color: #734432;
    margin-bottom: 15px;
}

.badge {
    padding: 8px 12px;
    font-weight: 500;
}

.table td {
    vertical-align: middle;
    font-size: 0.9rem;
}

.table-responsive {
    max-height: 500px;
    overflow-y: auto;
}

.table td, .table th {
    padding: 0.5rem;
}
</style>