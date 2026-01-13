<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.html');
    exit;
}

include('../config/connection.php');

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete related transactions
        $stmt = $conn->prepare("
            DELETE t FROM transactions t 
            INNER JOIN appointments a ON t.appointment_id = a.id 
            WHERE a.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Then delete related appointments
        $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM userdata WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        $success = "User and related data deleted successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch all users with their statistics
$stmt = $conn->prepare("
    SELECT 
        u.*, 
        COUNT(DISTINCT a.id) as total_appointments,
        COUNT(DISTINCT CASE WHEN a.status = 'Pending' THEN a.id END) as pending_appointments,
        COUNT(DISTINCT CASE WHEN a.status = 'Confirmed' THEN a.id END) as confirmed_appointments,
        COUNT(DISTINCT CASE WHEN a.status = 'Cancelled' THEN a.id END) as cancelled_appointments,
        COALESCE(SUM(t.amount), 0) as total_spent
    FROM userdata u 
    LEFT JOIN appointments a ON u.id = a.user_id 
    LEFT JOIN transactions t ON a.id = t.appointment_id
    GROUP BY u.id
");
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SilkSerenity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .user-container {
            padding: 2rem;
        }
        .table-container {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-container h3 {
            color: #734432;
            margin-bottom: 1.5rem;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        .modal-lg {
            max-width: 900px;
        }
        .badge {
            padding: 5px 10px;
        }
        #appointmentsDetails {
            max-height: 500px;
            overflow-y: auto;
        }
        .table td, .table th {
            vertical-align: middle;
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
                        <a class="nav-link active" href="admin_users.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_transactions.php">Transactions</a>
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

    <div class="user-container">
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
            <h3>User Management</h3>
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Total Appointments</th>
                        <th>Pending</th>
                        <th>Confirmed</th>
                        <th>Cancelled</th>
                        <th>Total Spent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $user['total_appointments']; ?></td>
                            <td><?php echo $user['pending_appointments']; ?></td>
                            <td><?php echo $user['confirmed_appointments']; ?></td>
                            <td><?php echo $user['cancelled_appointments']; ?></td>
                            <td>₱<?php echo number_format($user['total_spent'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info view-appointments" 
                                        data-user-id="<?php echo $user['id']; ?>">
                                    View Details
                                </button>
                                <a href="?delete=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal for User Appointments -->
        <div class="modal fade" id="appointmentsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">User Appointments Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="appointmentsDetails">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#usersTable').DataTable({
            pageLength: 25,
            language: {
                search: "Search users:"
            }
        });

        // Handle View Details button click
        $('.view-appointments').click(function() {
            const userId = $(this).data('user-id');
            const modal = new bootstrap.Modal(document.getElementById('appointmentsModal'));
            
            // Show modal
            modal.show();
            
            // Load appointment details
            $.ajax({
                url: 'get_user_appointments.php',
                method: 'GET',
                data: { user_id: userId },
                success: function(response) {
                    $('#appointmentsDetails').html(response);
                },
                error: function() {
                    $('#appointmentsDetails').html('<div class="alert alert-danger">Error loading appointment details.</div>');
                }
            });
        });
    });
    </script>
</body>
</html>