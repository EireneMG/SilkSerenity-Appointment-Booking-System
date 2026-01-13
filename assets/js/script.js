// Initialize all functionality when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Dropdown menu functionality
  initializeDropdown();

  // Contact page: Time slot generator
  generateTimeSlots();

  // Account page: Password visibility
  initializePasswordVisibility();

  // Write page: Star rating
  initializeStarRating();

  // Appointment Form Handling
  const appointmentForm = document.getElementById('appointmentForm');
  if (appointmentForm) {
      appointmentForm.addEventListener('submit', handleAppointmentSubmit);
  }

  // Load appointments if on account page
  const appointmentsTable = document.getElementById('appointmentsTableBody');
  if (appointmentsTable) {
      loadAppointments();
  }
});

// Contact page: Time slots
function generateTimeSlots() {
  const timeSelect = document.getElementById("appointmentTime");
  const dateInput = document.getElementById("appointmentDate");
  if (!timeSelect || !dateInput) return;

  // Set minimum date to today
  const today = new Date().toISOString().split('T')[0];
  dateInput.setAttribute('min', today);

  // Clear existing time slots when date changes
  dateInput.addEventListener('change', function() {
      updateAvailableTimeSlots();
  });
}

function updateAvailableTimeSlots() {
  const timeSelect = document.getElementById("appointmentTime");
  const dateInput = document.getElementById("appointmentDate");
  const selectedDate = dateInput.value;

  // Clear existing options
  timeSelect.innerHTML = '<option value="">Select Time</option>';

  if (!selectedDate) return;

  // Get current date and time
  const now = new Date();
  const selectedDateTime = new Date(selectedDate);

  // Check if selected date is in the past
  if (selectedDateTime < new Date(now.toDateString())) {
      alert("Please select a future date");
      dateInput.value = '';
      return;
  }

  // Fetch already booked appointments
  fetch(`../api/get_booked_slots.php?date=${selectedDate}`)
      .then(response => response.json())
      .then(bookedSlots => {
          // Fixed time slots from 8:00 AM to 8:00 PM every 2 hours
          const timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];

          timeSlots.forEach(timeString => {
              // Create date object for this time slot
              const slotDateTime = new Date(`${selectedDate}T${timeString}:00`);

              // Skip if time slot is in the past
              if (selectedDateTime.toDateString() === now.toDateString() && slotDateTime < now) {
                  return;
              }

              // Skip if time slot is already booked
              if (bookedSlots.includes(timeString)) {
                  return;
              }

              // Format display time
              const hour = parseInt(timeString.split(':')[0]);
              const minutes = timeString.split(':')[1];
              const ampm = hour >= 12 ? 'PM' : 'AM';
              const displayHour = hour > 12 ? hour - 12 : hour;
              const displayTime = `${displayHour}:${minutes} ${ampm}`;

              // Add the option to the dropdown
              const option = new Option(displayTime, timeString);
              timeSelect.add(option);
          });
      })
      .catch(error => {
          console.error('Error fetching booked slots:', error);
          alert('Error loading available time slots');
      });
}

// Account page: Password visibility
function initializePasswordVisibility() {
  const passwordInput = document.getElementById("password-input");
  const showPassword = document.getElementById("show-password");

  if (showPassword && passwordInput) {
    showPassword.addEventListener("click", () => {
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        showPassword.textContent = "Hide";
      } else {
        passwordInput.type = "password";
        showPassword.textContent = "Show";
      }
    });
  }
}

// Dropdown menu functionality
function initializeDropdown() {
  const userIcon = document.querySelector(".user-icon");
  const dropdownMenu = document.querySelector(".dropdown-menu");

  if (userIcon && dropdownMenu) {
    userIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle("show");
    });

    document.addEventListener("click", function () {
      if (dropdownMenu.classList.contains("show")) {
        dropdownMenu.classList.remove("show");
      }
    });

    dropdownMenu.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }
}

// Home page: Navigation
function goToContact() {
  window.location.href = "../pages/contact.php";
}

// Login page: Validation
function validateLoginForm() {
  let username = document.getElementById("username").value;
  let password = document.getElementById("password").value;
  if (username === "" || password === "") {
    alert("Please input your username and password!");
    return false;
  }
  return true;
}

// Signup page: Validation
function validateSignupForm() {
  let username = document.getElementById("username").value;
  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;

  if (username === "" || email === "" || password === "") {
    alert("All fields are required!");
    return false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address!");
    return false;
  }
  return true;
}

// Write page: Star rating
function initializeStarRating() {
  const stars = document.querySelectorAll(".clickable");
  if (!stars.length) return;

  let rating = 0;
  stars.forEach((star) => {
    star.addEventListener("click", () => {
      const starValue = parseInt(star.dataset.fill);

      // Toggle the state of the clicked star (empty -> half -> full -> empty)
      if (star.classList.contains("bi-star")) {
        star.classList.remove("bi-star");
        star.classList.add("bi-star-half");
        rating = starValue - 0.5;
      } else if (star.classList.contains("bi-star-half")) {
        star.classList.remove("bi-star-half");
        star.classList.add("bi-star-fill");
        rating = starValue;
      } else if (star.classList.contains("bi-star-fill")) {
        star.classList.remove("bi-star-fill");
        star.classList.add("bi-star");
        rating = starValue - 1;
      }

      // Update all other stars based on the new rating
      stars.forEach((otherStar) => {
        const otherStarValue = parseInt(otherStar.dataset.fill);

        if (otherStarValue < Math.ceil(rating)) {
          // Full-fill stars below the selected rating
          otherStar.classList.add("bi-star-fill");
          otherStar.classList.remove("bi-star", "bi-star-half");
        } else if (otherStarValue === Math.ceil(rating)) {
          // Handle the half-filled state
          if (rating % 1 === 0.5) {
            otherStar.classList.add("bi-star-half");
            otherStar.classList.remove("bi-star", "bi-star-fill");
          } else {
            otherStar.classList.add("bi-star-fill");
            otherStar.classList.remove("bi-star", "bi-star-half");
          }
        } else {
          // Empty stars above the selected rating
          otherStar.classList.add("bi-star");
          otherStar.classList.remove("bi-star-fill", "bi-star-half");
        }
      });
    });
  });
}

function handleAppointmentSubmit(e) {
  e.preventDefault();
  
  fetch('../api/book_appointment.php', {
      method: 'POST',
      body: new FormData(this)
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          alert('Appointment booked successfully!');
          window.location.href = '../views/account.html';
      } else {
          alert(data.message);
      }
  })
  .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while booking the appointment');
  });
}

function loadAppointments() {
  console.log('Loading appointments...'); // Debug log
  
  fetch('../api/get_appointments.php')
      .then(response => {
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return response.json();
      })
      .then(data => {
          console.log('Received data:', data); // Debug log
          
          const tableBody = document.getElementById('appointmentsTableBody');
          if (!tableBody) {
              console.error('Table body element not found');
              return;
          }
          
          tableBody.innerHTML = ''; // Clear existing content
          
          if (!data.success) {
              throw new Error(data.message || 'Error loading appointments');
          }
          
          if (!data.appointments || data.appointments.length === 0) {
              tableBody.innerHTML = `
                  <tr>
                      <td colspan="4" class="text-center">No appointments found</td>
                  </tr>
              `;
              return;
          }
          
          data.appointments.forEach(appointment => {
              const row = document.createElement('tr');
              row.innerHTML = `
                  <td>${appointment.service}</td>
                  <td>${appointment.appointment_date}</td>
                  <td>${appointment.appointment_time}</td>
                  <td class="status-${appointment.status.toLowerCase()}">${appointment.status}</td>
              `;
              tableBody.appendChild(row);
          });
      })
      .catch(error => {
          console.error('Error loading appointments:', error);
          const tableBody = document.getElementById('appointmentsTableBody');
          if (tableBody) {
              tableBody.innerHTML = `
                  <tr>
                      <td colspan="4" class="text-center text-danger">
                          Error loading appointments. Please try again later.
                      </td>
                  </tr>
              `;
          }
      });
}

// Make sure the function is called when the page loads
document.addEventListener('DOMContentLoaded', function() {
  const appointmentsTable = document.getElementById('appointmentsTableBody');
  if (appointmentsTable) {
      console.log('Found appointments table, loading data...'); // Debug log
      loadAppointments();
  }
});

// Form and Password validation
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  
  // Password matching validation
  const password = document.querySelector('#password');
  const confirmPassword = document.querySelector('#confirmPassword');
  
  if (password && confirmPassword) {
      const validatePassword = () => {
          if (password.value !== confirmPassword.value) {
              confirmPassword.setCustomValidity("Passwords do not match");
          } else {
              confirmPassword.setCustomValidity("");
          }
      };

      password.addEventListener("input", validatePassword);
      confirmPassword.addEventListener("input", validatePassword);
  }

  Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
          }
          form.classList.add('was-validated');
      }, false);
  });
})();

// Password visibility toggle
const togglePassword = document.querySelector('#togglePassword');
const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
const password = document.querySelector('#password');
const confirmPassword = document.querySelector('#confirmPassword');

if (togglePassword && password) {
  togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
  });
}

if (toggleConfirmPassword && confirmPassword) {
  toggleConfirmPassword.addEventListener('click', function () {
      const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPassword.setAttribute('type', type);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
  });
}

// Handle signup form submission
const signupForm = document.querySelector('.needs-validation');
if (signupForm) {
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        fetch('../auth/register.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '../views/login.html';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during registration');
        });
    });
}

// Handle login form submission
const loginForm = document.querySelector('form[action="../auth/authentication.php"]');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        fetch('../auth/authentication.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '../views/home.html';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during login');
        });
    });
}