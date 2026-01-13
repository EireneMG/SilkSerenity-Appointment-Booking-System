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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SilkSerenity - Services</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/mediaqueries.css" />
  <style>
    /* General Styles */
    body {
      background-color: #f3d8cf;
      color: #F2E6E2;
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Header Title */
    .header-title {
      text-align: center;
      font-family: 'Playfair Display', serif;
      font-size: 4rem;
      color: #734432;
      margin: 20px 0 10px;
    }

    /* Navbar Styles */
    .navbar {
      background-color: #734432;
      padding: 0.5rem 0;
    }

    .navbar-nav {
      justify-content: center;
      flex-grow: 1;
    }

    .navbar-nav .nav-link {
      font-family: 'Playfair Display', serif;
      color: #F2E6E2 !important;
      font-size: 1rem;
      font-weight: 500;
      margin: 0 15px; /* Adds spacing between links */
      padding: 0.5rem 1rem; /* Adds padding around links */
    }

    .navbar-nav .nav-link.active {
      font-family: 'Playfair Display', serif;
      font-weight: bold;
      color: #F2E6E2;
      border-radius: 3px;
    }

    .user-icon {
      font-size: 1.5rem;
      color: #F2E6E2;
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
            <a class="nav-link" href="../views/home.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../views/home.html#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../views/gallery.html">Gallery</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="services.php">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="contact.php">Contact</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="reviews.php">Reviews</a>
          </li>
        </ul>
        <div class="dropdown">
          <i class="user-icon bi bi-person-circle"></i>
          <div class="dropdown-menu">
            <a href="../views/account.html" class="dropdown-item">
              <i class="bi bi-gear"></i> Settings
            </a>
            <a href="../auth/logout.php" class="dropdown-item">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="services-section">
    <?php foreach ($services as $service): ?>
    <div class="service-item">
      <h2><?php echo htmlspecialchars($service['service_name']); ?></h2>
      <p>₱ <?php echo number_format($service['price'], 2); ?></p>
      <img src="../assets/images/services<?php echo $service['id']; ?>.svg" alt="<?php echo htmlspecialchars($service['service_name']); ?>">
      <p><?php echo htmlspecialchars($service['description']); ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  
    <!-- Reverse Footer Section -->
    <footer class="footer-reverse">
      <div class="footer-container-reverse">
        <!-- Logo and Social Links -->
        <div class="footer-logo-social-reverse">
          <h2 class="footer-logo-reverse">SilkSerenity</h2>
        </div>

        <!-- Navigation Links -->
        <div class="footer-links-reverse">
          <ul>
            <li><a href="../views/home.html#about">About</a></li>
            <li><a href="#">Store Locator</a></li>
            <li><a href="contact.php">Contact Us</a></li>
          </ul>
          <ul>
            <li><a href="#">FAQs</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms & Conditions</a></li>
          </ul>
        </div>

        <!-- Subscription Form -->
        <div class="footer-subscription-reverse text-start">
          <p>Get the latest updates and receive exclusive offers when you subscribe!</p>
          <form action="#" method="post" class="subscribe-form-reverse">
            <input type="email" placeholder="Your email" required>
            <button type="submit">></button>
          </form>
        </div>
      </div>
      <div class="social-icons-reverse text-center">
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-tiktok"></i></a>
      </div>
      <div class="footer-bottom-reverse">
        <p>&copy; 2024, SilkSerenity</p>
      </div>
    </footer>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> 
  <script src="../assets/js/script.js"></script>
</body>
</html>