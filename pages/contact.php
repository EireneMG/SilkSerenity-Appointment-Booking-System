<?php
include('../config/connection.php');

// Fetch services from database
$stmt = $conn->prepare("SELECT * FROM services ORDER BY id");
$stmt->execute();
$services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SilkSerenity - Appointment</title>
    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="mediaqueries.css" />
    <style>
      /* General Styles */
      body {
        background-color: #734432;
        color: #f2e6e2;
        font-family: "Arial", sans-serif;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>

  <body>
    <!-- Header Title -->
    <header>
      <div class="header-title">SilkSerenity</div>
    </header>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <!-- Add the toggler button -->
        <button 
          class="navbar-toggler" 
          type="button" 
          data-bs-toggle="collapse" 
          data-bs-target="#navbarNav" 
          aria-controls="navbarNav" 
          aria-expanded="false" 
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
    
        <!-- Make sure your navigation has the collapse class -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="home.html">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="home.html#about">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="gallery.html">Gallery</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="services.php">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="contact.php">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="reviews.php">Reviews</a>
            </li>
          </ul>
          <div class="dropdown">
            <i class="user-icon bi bi-person-circle"></i>
            <div class="dropdown-menu">
              <a href="account.html" class="dropdown-item">
                <i class="bi bi-gear"></i> Settings
              </a>
              <a href="logout.php" class="dropdown-item">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Contact Section -->
    <div class="contact-container">
      <div class="row">
        <div class="col-sm">
          <div class="contact-section">
            <h3>Contact</h3>
            <div class="contact-item">
              <i class="bi bi-facebook"></i>
              <a href="#">De An Na (Da iane)</a>
            </div>
            <div class="contact-item">
              <i class="bi bi-envelope-fill"></i>
              <a href="mailto:yanyantanaleon1@gmail.com"
                >yanyantanaleon1@gmail.com</a
              >
            </div>
            <div class="contact-item">
              <i class="bi bi-telephone-fill"></i>
              <p>0993-900-1866 / 09055103342</p>
            </div>
            <br /><br /><br /><br /><br />
            <p class="contact-note">
              To avoid potential rescheduling or cancellation, please arrive at
              least 30 minutes before your scheduled appointment time.
            </p>
          </div>
        </div>

        <!-- Appointment Form -->
        <div class="col-sm">
          <div class="appointment-form">
            <h3>Appointment Form</h3>
            <form action="book_appointment.php" method="POST" id="appointmentForm">
              <!-- First and Last Name -->
              <div class="form-group-row">
                <div class="form-group">
                  <label for="firstName">First name*</label>
                  <input type="text" id="firstName" name="firstName" required />
                </div>
                <div class="form-group">
                  <label for="lastName">Last name*</label>
                  <input type="text" id="lastName" name="lastName" required />
                </div>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" required />
              </div>

              <!-- Phone Number -->
              <div class="form-group">
                <label for="phone">Phone number*</label>
                <input type="tel" id="phone" name="phone" required />
              </div>

              <!-- Add these fields inside the appointment form, after the phone number field -->
              <div class="form-group">
                <label for="address">Address*</label>
                <input
                  type="text"
                  id="address"
                  name="address"
                  placeholder="Enter your complete address"
                  required
                />
              </div>

              <div class="form-group">
                <label for="age">Age*</label>
                <input
                  type="number"
                  id="age"
                  name="age"
                  min="18"
                  max="100"
                  placeholder="Enter your age"
                  required
                />
              </div>
              <!-- Add Service Selection -->
              <div class="form-group">
                <label for="service">Service*</label>
                <select
                  class="form-select"
                  id="service"
                  name="service"
                  required
                >
                  <option value="">Select a service</option>
                  <?php foreach ($services as $service): ?>
                  <option value="<?php echo htmlspecialchars($service['service_name']); ?>">
                    <?php echo htmlspecialchars($service['service_name']); ?> - ₱<?php echo number_format($service['price'], 2); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="source">How did you hear about us?</label>
                <select id="source" name="source" class="form-select">
                  <option value="">Select an option</option>
                  <option value="social">Social Media</option>
                  <option value="friend">Friend Referral</option>
                  <option value="search">Search Engine</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <!-- Date and Time -->
              <div class="form-group">
                <label for="appointmentDate">Date & Time*</label>
                <div class="form-group-row">
                  <input
                    type="date"
                    id="appointmentDate"
                    name="appointmentDate"
                    required
                  />
                  <select id="appointmentTime" name="appointmentTime" required>
                    <option value="">Select Time</option>
                  </select>
                </div>
              </div>

              <!-- Buttons -->
              <div class="form-actions">
                <button type="reset" class="clear-btn">Clear</button>
                <button type="submit" class="book-btn">Book</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
      <div class="footer-container">
        <!-- Logo and Social Links -->
        <div class="footer-logo-social">
          <h2 class="footer-logo">SilkSerenity</h2>
        </div>

        <!-- Navigation Links -->
        <div class="footer-links">
          <ul>
            <li><a href="home.html#about">About</a></li>
            <li><a href="#">Store Locator</a></li>
            <li><a href="contact.html">Contact Us</a></li>
          </ul>
          <ul>
            <li><a href="#">FAQs</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms & Conditions</a></li>
          </ul>
        </div>

        <!-- Subscription Form -->
        <div class="footer-subscription text-start">
          <p>
            Get the latest updates and receive exclusive offers when you
            subscribe!
          </p>
          <form action="#" method="post" class="subscribe-form">
            <input type="email" placeholder="Your email" required />
            <button type="submit">></button>
          </form>
        </div>
      </div>
      <div class="social-icons text-center">
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-tiktok"></i></a>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024, SilkSerenity</p>
      </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
      rel="stylesheet"
    />
    <script src="script.js"></script>
  </body>
</html>
