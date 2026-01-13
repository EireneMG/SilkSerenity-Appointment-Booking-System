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
            case 'update':
                $stmt = $conn->prepare("UPDATE services SET price = ?, description = ? WHERE id = ?");
                $stmt->bind_param("dsi", $_POST['price'], $_POST['description'], $_POST['service_id']);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Service updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update service']);
                }
                exit;
        }
    }
}

// Debug information
$debug = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Management - SilkSerenity</title>
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
        #successMessage, #errorMessage {
            display: none;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            display: none;
        }
        .price-cell {
            font-weight: bold;
            color: #28a745;
        }
        .description-cell {
            max-width: 300px;
            white-space: pre-wrap;
            word-wrap: break-word;
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
                        <a class="nav-link" href="admin_manage.php">Admin Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_services.php">Service Management</a>
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

        <h2>Service Management</h2>
        
        <!-- Debug Information -->
        <div class="debug-info">
            <h4>Debug Information</h4>
            <?php
            try {
                $check_table = $conn->query("SHOW TABLES LIKE 'services'");
                $debug[] = "Table exists: " . ($check_table->num_rows > 0 ? "Yes" : "No");
                
                if ($check_table->num_rows > 0) {
                    $count = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
                    $debug[] = "Number of services: " . $count;
                }
            } catch (Exception $e) {
                $debug[] = "Error: " . $e->getMessage();
            }
            
            foreach ($debug as $message) {
                echo "<p>$message</p>";
            }
            ?>
        </div>
        
        <!-- Services Table -->
        <div class="admin-card">
            <h3>Current Services</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Price (₱)</th>
                        <th>Description</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->prepare("SELECT * FROM services ORDER BY id");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 0) {
                            echo "<tr><td colspan='5' class='text-center'>No services found. Please run populate_services.php to add initial services.</td></tr>";
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$row['service_name']}</td>";
                                echo "<td class='price-cell'>₱" . number_format($row['price'], 2) . "</td>";
                                echo "<td class='description-cell'>{$row['description']}</td>";
                                echo "<td>{$row['updated_at']}</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary edit-btn' 
                                            data-id='{$row['id']}'
                                            data-price='{$row['price']}'
                                            data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "'
                                        >
                                            Edit
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='5' class='text-center text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editServiceForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="service_id" id="editServiceId">
                        
                        <div class="mb-3">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" class="form-control" name="price" id="editPrice" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Show debug info on double click of the header
        $('h2').dblclick(function() {
            $('.debug-info').toggle();
        });

        // Handle edit button clicks
        $('.edit-btn').click(function() {
            const id = $(this).data('id');
            const price = $(this).data('price');
            const description = $(this).data('description');
            
            $('#editServiceId').val(id);
            $('#editPrice').val(price);
            $('#editDescription').val(description);
            
            $('#editServiceModal').modal('show');
        });

        // Handle form submission
        $('#editServiceForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'admin_services.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#successMessage').text(response.message).show();
                        $('#editServiceModal').modal('hide');
                        // Reload page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#errorMessage').text(response.message).show();
                    }
                },
                error: function() {
                    $('#errorMessage').text('Failed to update service.').show();
                }
            });
        });
    });
    </script>
</body>
</html>