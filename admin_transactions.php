<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.html');
    exit;
}

include('../config/connection.php');

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $transaction_id = $_POST['transaction_id'];
    $payment_method = $_POST['payment_method'];
    
    // Update the transaction status
    $stmt = $conn->prepare("UPDATE transactions SET payment_status = 'Confirmed', payment_method = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_method, $transaction_id);
    
    if ($stmt->execute()) {
        // Get the appointment ID associated with the transaction
        $stmt = $conn->prepare("SELECT appointment_id FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $appointment_id = $row['appointment_id'];
            
            // Update the appointment status to 'Completed'
            $stmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
        }
        
        $success = "Payment confirmed successfully and appointment marked as completed!";
    } else {
        $error = "Error confirming payment.";
    }
}

// Add new transaction
if (isset($_POST['add_transaction'])) {
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    
    $stmt = $conn->prepare("INSERT INTO transactions (appointment_id, amount, payment_method) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $appointment_id, $amount, $payment_method);
    
    if ($stmt->execute()) {
        $success = "Transaction added successfully!";
    } else {
        $error = "Error adding transaction.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Management - SilkSerenity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .transaction-container {
            padding: 2rem;
        }
        .table-container {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-Pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-Confirmed {
            color: #28a745;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #734432;
            border-color: #734432;
        }
        .btn-primary:hover {
            background-color: #5a352a;
            border-color: #5a352a;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #734432;">
        <div class="container">
            <a class="navbar-brand" href="#">SilkSerenity Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_users.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_analytics.php">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_manage.php">Admin Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_services.php">Service Management</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="transaction-container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Transactions</h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                    Add Transaction
                </button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT t.*, a.first_name, a.last_name, a.service
                        FROM transactions t
                        JOIN appointments a ON t.appointment_id = a.id
                        ORDER BY t.created_at DESC
                    ");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                            <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td class="status-<?php echo $row['payment_status']; ?>">
                                <?php echo $row['payment_status']; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['payment_status'] === 'Pending'): ?>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmPaymentModal"
                                            data-transaction-id="<?php echo $row['id']; ?>">
                                        Confirm Payment
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Appointment</label>
                            <select name="appointment_id" class="form-select" required>
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT a.id, a.first_name, a.last_name, a.service
                                    FROM appointments a
                                    LEFT JOIN transactions t ON a.id = t.appointment_id
                                    WHERE t.id IS NULL
                                    AND a.status = 'Confirmed'
                                ");
                                $stmt->execute();
                                $appointments = $stmt->get_result();
                                while ($apt = $appointments->fetch_assoc()) {
                                    echo "<option value='{$apt['id']}'>";
                                    echo htmlspecialchars("{$apt['first_name']} {$apt['last_name']} - {$apt['service']}");
                                    echo "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_transaction" class="btn btn-primary">Add Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="transaction_id" id="confirmTransactionId">
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="confirm_payment" class="btn btn-success">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set transaction ID when opening confirm payment modal
        document.getElementById('confirmPaymentModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var transactionId = button.getAttribute('data-transaction-id');
            document.getElementById('confirmTransactionId').value = transactionId;
        });
    </script>
</body>
</html>