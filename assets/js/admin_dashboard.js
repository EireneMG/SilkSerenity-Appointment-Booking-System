document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    initializeCharts();
});

function loadDashboardData() {
    fetch('../api/get_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.data);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateDashboardStats(data) {
    document.getElementById('todayAppointments').textContent = data.today_appointments;
    document.getElementById('totalCustomers').textContent = data.total_customers;
    document.getElementById('weeklyRevenue').textContent = `$${data.weekly_revenue}`;
}

function initializeCharts() {
    // Appointments Chart
    new Chart(document.getElementById('appointmentsChart'), {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Weekly Appointments',
                data: [12, 19, 3, 5, 2, 3, 7],
                borderColor: 'rgb(75, 192, 192)'
            }]
        }
    });

    // Revenue Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: ['Service A', 'Service B', 'Service C', 'Service D'],
            datasets: [{
                label: 'Revenue by Service',
                data: [1200, 1900, 300, 500],
                backgroundColor: 'rgba(54, 162, 235, 0.5)'
            }]
        }
    });
}

$(document).ready(function() {
    $('#appointmentsTable').DataTable({
        pageLength: 25,
        order: [[2, 'desc'], [3, 'desc']], // Default sort by date (column 2) and time (column 3) in descending order
        language: {
            search: "Search users:"
        },
        columnDefs: [
            {
                targets: [2, 3], // Date and Time columns
                orderData: [2, 3] // When sorting these columns, maintain the other column's sort
            }
        ]
    });

    // Initialize DataTable for Recent Transactions
    $('#recentTransactionsTable').DataTable({
        pageLength: 5,
        order: [[4, 'desc'], [5, 'asc']],
        language: {
            search: "Search transactions:"
        }
    });
});