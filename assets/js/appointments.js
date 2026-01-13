document.addEventListener('DOMContentLoaded', function() {
    loadAppointments();
    initializeFilters();
});

function loadAppointments(filters = {}) {
    fetch('../api/get_admin_appointments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(filters)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateAppointmentsTable(data.appointments);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateAppointmentsTable(appointments) {
    const tableBody = document.getElementById('appointmentsTableBody');
    tableBody.innerHTML = '';

    appointments.forEach(apt => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${apt.appointment_date}</td>
            <td>${apt.appointment_time}</td>
            <td>${apt.first_name} ${apt.last_name}</td>
            <td>${apt.service}</td>
            <td>
                <select onchange="updateStatus(${apt.id}, this.value)" class="form-select form-select-sm">
                    <option value="Pending" ${apt.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Confirmed" ${apt.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                    <option value="Completed" ${apt.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    <option value="Cancelled" ${apt.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewDetails(${apt.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteAppointment(${apt.id})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function updateStatus(id, status) {
    fetch('../api/update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id, status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status updated successfully');
        }
    })
    .catch(error => console.error('Error:', error));
}