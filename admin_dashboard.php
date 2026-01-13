<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../views/admin_login.html');
    exit;
}

include('../config/connection.php');

// Fetch today's appointments
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) as today_count FROM appointments WHERE appointment_date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$today_result = $stmt->get_result()->fetch_assoc();
$today_appointments = $today_result['today_count'];

// Fetch today's revenue
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as today_revenue 
                       FROM transactions 
                       WHERE transaction_date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$revenue_result = $stmt->get_result()->fetch_assoc();
$today_revenue = $revenue_result['today_revenue'];

// Fetch weekly revenue
$week_start = date('Y-m-d', strtotime('monday this week'));
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as weekly_revenue 
                       FROM transactions 
                       WHERE transaction_date >= ?");
$stmt->bind_param("s", $week_start);
$stmt->execute();
$weekly_result = $stmt->get_result()->fetch_assoc();
$weekly_revenue = $weekly_result['weekly_revenue'];

// Fetch monthly revenue
$month_start = date('Y-m-01');
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as monthly_revenue 
                       FROM transactions 
                       WHERE transaction_date >= ?");
$stmt->bind_param("s", $month_start);
$stmt->execute();
$monthly_result = $stmt->get_result()->fetch_assoc();
$monthly_revenue = $monthly_result['monthly_revenue'];

// Fetch pending appointments
$stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM appointments WHERE status = 'Pending'");
$stmt->execute();
$pending_result = $stmt->get_result()->fetch_assoc();
$pending_appointments = $pending_result['pending_count'];

// Fetch total users
$stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM userdata");
$stmt->execute();
$users_result = $stmt->get_result()->fetch_assoc();
$total_users = $users_result['user_count'];

// Handle report generation request
if (isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'] ?? date('Y-01-01');
    $endDate = $_POST['end_date'] ?? date('Y-12-31');

    $filename = "Sales_Report_{$reportType}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Set headers for CSV based on report type
    if ($reportType === 'monthly' || $reportType === 'yearly') {
        fputcsv($output, array('Transaction Date', 'Amount in Pesos'));
    } elseif ($reportType === 'weekly') {
        fputcsv($output, array('Week Starting', 'Amount in Pesos'));
    } else {
        fputcsv($output, array('Transaction Date', 'Transaction ID', 'Name', 'Payment Method', 'Amount in Pesos'));
    }

    // Function to fill missing dates/weeks/months with zero sales
    function fillDates($results, $startDate, $endDate, $intervalFormat, $reportType) {
        $filledResults = [];
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        // Adjust for weekly reports: calculate weeks starting from the given date
        if ($reportType === 'weekly') {
            $start->modify('monday this week'); // Move to the first Monday of the week
            $end->modify('sunday this week');  // Move to the last Sunday of the week
        }

        $period = new DatePeriod(
            $start,
            new DateInterval($intervalFormat),
            $end->modify('+1 day') // Include the end date
        );

        foreach ($period as $date) {
            $dateKey = match ($reportType) {
                'weekly' => $date->format('Y-m-d'), // Week starts from Monday
                'monthly' => $date->format('Y-m-01'),
                'yearly' => $date->format('Y-01-01'),
                default => $date->format('Y-m-d'),
            };

            if (!isset($results[$dateKey])) {
                $filledResults[$dateKey] = [['transaction_date' => $dateKey, 'amount' => 0]];
            } else {
                $filledResults[$dateKey] = $results[$dateKey];
            }
        }

        return $filledResults;
    }

    // Query logic
    $groupBy = match ($reportType) {
        'weekly' => "YEAR(transaction_date), WEEK(transaction_date, 1)", // Group by ISO week (1 = Monday start)
        'monthly' => "YEAR(transaction_date), MONTH(transaction_date)",
        'yearly' => "YEAR(transaction_date)",
        default => null
    };

    if ($reportType === 'weekly' || $reportType === 'monthly' || $reportType === 'yearly') {
        $query = "SELECT " . match ($reportType) {
            'weekly' => "DATE_FORMAT(transaction_date - INTERVAL WEEKDAY(transaction_date) DAY, '%Y-%m-%d') AS transaction_date",
            'monthly' => "DATE_FORMAT(transaction_date, '%Y-%m-01') AS transaction_date",
            'yearly' => "DATE_FORMAT(transaction_date, '%Y-01-01') AS transaction_date",
            default => "transaction_date"
        } . ",
                         SUM(t.amount) AS amount
                  FROM transactions t
                  WHERE transaction_date BETWEEN ? AND ?
                  GROUP BY $groupBy
                  ORDER BY transaction_date";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = [];
        $totalSales = 0;

        while ($row = $result->fetch_assoc()) {
            $results[$row['transaction_date']][] = ['transaction_date' => $row['transaction_date'], 'amount' => $row['amount']];
            $totalSales += $row['amount'];
        }
    }

    // Fill missing dates for reports
    if ($reportType !== 'custom') {
        $intervalFormat = match ($reportType) {
            'daily' => 'P1D',
            'weekly' => 'P7D', // Weekly intervals
            'monthly' => 'P1M',
            'yearly' => 'P1Y',
            default => null
        };
        $results = fillDates($results, $startDate, $endDate, $intervalFormat, $reportType);
    }

    // Write data to CSV
    foreach ($results as $dateResults) {
        foreach ($dateResults as $result) {
            if ($reportType === 'weekly' || $reportType === 'monthly' || $reportType === 'yearly') {
                fputcsv($output, array($result['transaction_date'], number_format($result['amount'], 2)));
            } else {
                fputcsv($output, array(
                    $result['transaction_date'], 
                    $result['transaction_id'] ?? 'None', 
                    $result['name'] ?? 'None', 
                    $result['payment_method'] ?? 'None', 
                    number_format($result['amount'], 2)
                ));
            }
        }
    }

    // Add total sales row
    fputcsv($output, $reportType === 'weekly' || $reportType === 'monthly' || $reportType === 'yearly' 
        ? array('Total Sales', number_format($totalSales, 2)) 
        : array('Total Sales', '', '', '', number_format($totalSales, 2))
    );

    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SilkSerenity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .dashboard-container {
            padding: 2rem;
        }
        .stats-card {
            background-color: #f3d8cf;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            height: 100%;
        }
        .stats-card h3 {
            color: #734432;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .stats-card h2 {
            color: #4a2c22;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .stats-card p {
            margin-bottom: 0.3rem;
            color: #666;
            font-size: 0.9rem;
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
        .status-Pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-Confirmed {
            color: #28a745;
            font-weight: bold;
        }
        .status-Cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .table th {
            color: #734432;
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
                        <a class="nav-link active" href="admin_dashboard.php">Dashboard</a>
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

    <div class="dashboard-container">
        <!-- Report Generation Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-container">
                    <h3>Generate Sales Report</h3>
                    <form action="admin_dashboard.php" method="post">
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type:</label>
                            <select name="report_type" class="form-select" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="custom">Custom Date Range</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date:</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date:</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>Today's Revenue</h3>
                    <h2>₱<?php echo number_format($today_revenue, 2); ?></h2>
                    <p><?php echo $today_appointments; ?> appointments today</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>This Week's Revenue</h3>
                    <h2>₱<?php echo number_format($weekly_revenue, 2); ?></h2>
                    <p>Since <?php echo date('M d', strtotime('monday this week')); ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>This Month's Revenue</h3>
                    <h2>₱<?php echo number_format($monthly_revenue, 2); ?></h2>
                    <p>Since <?php echo date('M d', strtotime('first day of this month')); ?></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>Payment Methods Today</h3>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT payment_method, COUNT(*) as count
                        FROM transactions 
                        WHERE transaction_date = ?
                        GROUP BY payment_method
                    ");
                    $stmt->bind_param("s", $today);
                    $stmt->execute();
                    $payment_methods = $stmt->get_result();
                    while ($method = $payment_methods->fetch_assoc()) {
                        echo "<p>{$method['payment_method']}: {$method['count']}</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

<!--All Appointments-->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-container">
            <h3>All Appointments</h3>
            <table class="table table-hover" id="appointmentsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = $conn->prepare("
                        SELECT 
                            a.*,
                            t.payment_status,
                            t.amount
                        FROM appointments a
                        LEFT JOIN transactions t ON a.id = t.appointment_id
                        ORDER BY STR_TO_DATE(a.appointment_date, '%Y-%m-%d') DESC, 
                                 STR_TO_DATE(a.appointment_time, '%H:%i:%s') DESC
                    ");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                        echo "<td>{$row['service']}</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['appointment_date'])) . "</td>";
                        echo "<td>" . date('h:i A', strtotime($row['appointment_time'])) . "</td>";
                        echo "<td>{$row['phone']}</td>";
                        echo "<td class='status-{$row['status']}'>{$row['status']}</td>";
                        echo "<td>" . ($row['payment_status'] ?? 'Pending') . "</td>";
                        echo "<td>₱" . number_format($row['amount'] ?? 0, 2) . "</td>";
                        echo "<td>";
                        if ($row['status'] === 'Pending') {
                            echo "<a href='../api/update_status.php?id={$row['id']}&status=Confirmed' 
                                    class='btn btn-sm btn-success me-2' 
                                    onclick=\"return confirm('Are you sure you want to confirm this appointment?');\">Confirm</a>";
                            echo "<a href='../api/update_status.php?id={$row['id']}&status=Cancelled' 
                                    class='btn btn-sm btn-danger' 
                                    onclick=\"return confirm('Are you sure you want to cancel this appointment?');\">Cancel</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

        <!-- Recent Transactions Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-container">
                    <h3>Recent Transactions</h3>
                    <table class="table" id="recentTransactionsTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT t.*, a.first_name, a.last_name, a.service 
                                FROM transactions t
                                JOIN appointments a ON t.appointment_id = a.id
                                ORDER BY t.created_at DESC 
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$row['first_name']} {$row['last_name']}</td>";
                                echo "<td>{$row['service']}</td>";
                                echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                                echo "<td>{$row['payment_method']}</td>";
                                echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                echo "<td>" . date('h:i A', strtotime($row['created_at'])) . "</td>"; 
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add DataTables CSS and JS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('#appointmentsTable').DataTable({
    pageLength: 25,
    order: [[2, 'desc'], [3, 'desc']], // Sort by Date (column 2) and Time (column 3) in descending order
    language: {
        search: "Search users:"
    },
    columnDefs: [
        {
            targets: 2, // Date column
            type: 'date'
        }
    ]
});

    // Initialize DataTable for Recent Transactions
    $('#recentTransactionsTable').DataTable({
        pageLength: 5, // Set the number of rows to display
        order: [[4, 'desc'], [5, 'asc']], // Sort by Date (column 4) and then by Time (column 5)
        language: {
            search: "Search transactions:"
        }
    });
});
</script>
</body>
</html>