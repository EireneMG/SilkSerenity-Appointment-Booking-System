<?php
session_start(); // Start the session
include('../config/connection.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Fetch completed appointments count for the user
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND status = 'Completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($completed_count);
$stmt->fetch();
$stmt->close(); // Close the statement after fetching the count

// Fetch all reviews from the database
$stmt = $conn->prepare("
    SELECT r.*, a.service, a.first_name, a.last_name 
    FROM reviews r 
    JOIN appointments a ON r.appointment_id = a.id 
    ORDER BY r.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SilkSerenity - Reviews</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
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
      .reviews-section {
        padding: 2rem;
        background-color: #bf8674;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin: 2rem auto;
        width: 80%;
        max-width: 600px;
      }
      .review {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border: 1px solid #bf8674;
        border-radius: 10px;
      }
      .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .reviewer-info {
        display: flex;
        align-items: center;
      }
      .stars {
        color: #ffcc00;
      }
      .review-date {
        font-size: 0.9rem;
        color: white;
      }
      .write-review-btn {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background-color: #734432;
        color: #fff;
        border-radius: 5px;
        text-decoration: none;
      }
      .write-review-btn:hover {
        background-color: #bb5e5e;
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
              <a class="nav-link" href="contact.php">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="reviews.php">Reviews</a>
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

    <!-- Reviews Section -->
    <div class="reviews-section">
      <?php while ($row = $result->fetch_assoc()): ?>
        <section class="review">
          <div class="review-header">
            <div class="reviewer-info">
              <div>
                <h4><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                <div class="stars">
                  <?php for ($i = 0; $i < $row['rating']; $i++): ?>
                    <i class="bi bi-star-fill"></i>
                  <?php endfor; ?>
                  <?php for ($i = $row['rating']; $i < 5; $i++): ?>
                    <i class="bi bi-star"></i>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
            <div class="review-date"><?php echo date('F j, Y', strtotime($row['created_at'])); ?></div>
          </div>
          <p><?php echo htmlspecialchars($row['review_text']); ?></p>
          <p><strong>Service Reviewed:</strong> <?php echo htmlspecialchars($row['service']); ?></p> <!-- Display the service -->
        </section>
      <?php endwhile; ?>

      <!-- Write a Review Button -->
      <div class="write-review">
        <?php if ($completed_count > 0): ?>
          <a href="write.php" class="write-review-btn">Write a Review</a>
        <?php else: ?>
          <div class="alert alert-warning mt-3">You cannot write a review because you do not have any completed appointments.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
      <div class="footer-container">
        <div class="footer-logo-social">
          <h2 class="footer-logo">SilkSerenity</h2>
        </div>
        <div class="footer-links">
          <ul>
            <li><a href="home.html#about">About</a></li>
            <li><a href="#">Store Locator</a></li>
            <li><a href="contact.php">Contact Us</a></li>
          </ul>
          <ul>
            <li><a href="#">FAQs</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms & Conditions</a></li>
          </ul>
        </div>
        <div class="footer-subscription text-start">
          <p>Get the latest updates and receive exclusive offers when you subscribe!</p>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="script.js"></script>
</body>
</html>