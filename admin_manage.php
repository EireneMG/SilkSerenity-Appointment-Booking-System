<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.html');
    exit;
}

include('../config/connection.php');

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Validate password strength
                if (strlen($_POST['password']) < 8) {
                    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
                    exit;
                }

                // Check if the email already exists
                $email = $_POST['email'];
                $email_check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin WHERE email = ?");
                $email_check_stmt->bind_param("s", $email);
                $email_check_stmt->execute();
                $email_check_result = $email_check_stmt->get_result();
                $email_exists = $email_check_result->fetch_assoc()['count'] > 0;

                if ($email_exists) {
                    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
                    exit;
                }

                // Hash the password before storing
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // Insert new admin
                $stmt = $conn->prepare("INSERT INTO admin (username, password, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $_POST['username'], $hashed_password, $email);
                
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Admin added successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Database insertion failed']);
                }
                exit;
                
            case 'update':
                $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['username'], $_POST['email'], $_POST['admin_id']);
                $stmt->execute();
                break;
                
            case 'delete':
                // Prevent deleting the last admin
                $count = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
                if ($count > 1) {
                    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
                    $stmt->bind_param("i", $_POST['admin_id']);
                    $stmt->execute();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Cannot delete the last admin']);
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - SilkSerenity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-container {
            padding: 2rem;
        }
        .admin-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        #emailFeedback, #passwordFeedback, #confirmPasswordFeedback {
            color: red;
            display: none;
        }
        #successMessage, #errorMessage {
            display: none;
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
                        <a class="nav-link" href="admin_transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_analytics.php">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_manage.php">Admin Management</a>
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
    
    <div class="admin-container">
        <div id="successMessage" class="alert alert-success" role="alert"></div>
        <div id="errorMessage" class="alert alert-danger" role="alert"></div>

        <h2>Admin Management</h2>
        
        <!-- Add New Admin Form -->
        <div class="admin-card">
            <h3>Add New Admin</h3>
            <form id="addAdminForm" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="adminPassword" required>
                    <div id="passwordFeedback">Password must be at least 8 characters long.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" required>
                    <div id="confirmPasswordFeedback">Passwords do not match.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="adminEmail" required>
                    <div id="emailFeedback">Email already exists. Please use a different email.</div>
                </div>
                <button type="submit" class="btn btn-primary">Add Admin</button>
            </form>
        </div>

        <!-- Existing Admins -->
        <div class="admin-card">
            <h3>Current Admins</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM admin");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['username']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "<td>";
                        if ($row['id'] !== $_SESSION['admin_id']) {
                            echo "<form method='POST' style='display:inline;'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='admin_id' value='{$row['id']}'>
                                    <button type='submit' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this admin?\")'>Delete</button>
                                  </form>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Clear any existing messages on page load
        $('#successMessage, #errorMessage').hide();

        // Check if email exists
        $('#adminEmail').on('blur', function() {
            var email = $(this).val();
            if (email) {
                $.ajax({
                    url: 'check_email.php',
                    type: 'POST',
                    data: { email: email },
                    success: function(response) {
                        if (response == 'exists') {
                            $('#emailFeedback').show();
                        } else {
                            $('#emailFeedback').hide();
                        }
                    }
                });
            }
        });

        // Handle form submission
        $('#addAdminForm').on('submit', function(e) {
            e.preventDefault();
            var emailFeedback = $('#emailFeedback').is(':visible');
            var password = $('#adminPassword').val();
            var confirmPassword = $('#confirmPassword').val();

            // Reset previous messages
            $('#successMessage, #errorMessage').hide();

            // Validate password length
            if (password.length < 8) {
                $('#passwordFeedback').show();
                return;
            } else {
                $('#passwordFeedback').hide();
            }

            // Validate password confirmation
            if (password !== confirmPassword) {
                $('#confirmPasswordFeedback').show();
                return;
            } else {
                $('#confirmPasswordFeedback').hide();
            }

            // If validation passes, submit the form via AJAX
            if (!emailFeedback) {
                $.ajax({
                    url: 'admin_manage.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === "success") {
                            $('#successMessage').text(response.message).show();
                            // Clear form
                            $('#addAdminForm')[0].reset();
                            // Reload page after a short delay
                            setTimeout(function() {
                                location.reload(true);
                            }, 1500);
                        } else {
                            $('#errorMessage').text(response.message).show();
                        }
                    },
                    error: function() {
                        $('#errorMessage').text('Failed to add admin.').show();
                    }
                });
            } else {
                $('#errorMessage').text('Please fix the errors before submitting.').show();
            }
        });
    });
    </script>
</body>
</html>