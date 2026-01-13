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

// Fetch completed appointments for the user
$stmt = $conn->prepare("SELECT id, service, appointment_date FROM appointments WHERE user_id = ? AND status = 'Completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Write a Review</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="mediaqueries.css" />
    <style>
      body {
        font-family: "Arial", sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f3d8cf;
      }
      .review-container {
        width: 80%;
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
      }
      .back-icon {
        font-size: 1.5rem;
        color: #734432;
        cursor: pointer;
        position: absolute;
        top: 1rem;
        left: 1rem;
      }
      .back-icon:hover {
        color: #bb5e5e;
      }
      h1 {
        text-align: center;
        font-family: "Playfair Display", serif;
        font-size: 1.8rem;
        color: #734432;
        margin-bottom: 1rem;
      }
      .rate {
        text-align: center;
        margin-bottom: 1rem;
      }
      .stars {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
      }
      .stars i {
        font-size: 2rem;
        color: #ffcc00;
        cursor: pointer;
        transition: transform 0.2s ease;
      }
      .textarea-container {
        margin-top: 1.5rem;
      }
      .textarea-container textarea {
        width: 100%;
        height: 100px;
        padding: 1rem;
        font-size: 1rem;
        color: #734432;
        border: 1px solid #734432;
        border-radius: 10px;
        resize: none;
      }
      .post-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 3rem;
        padding-right: 1rem;
      }
      .cancel-button {
        padding: 0.8rem 2rem;
        border: 1px solid #734432;
        border-radius: 12px;
        font-size: 1rem;
        cursor: pointer;
        color: #734432;
        background-color: #f3d8cf;
      }
      .post-button {
        padding: 0.8rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        cursor: pointer;
        color: #fff;
        background-color: #734432;
      }
    </style>
</head>

<body>
    <div class="review-container">
      <!-- Back Icon -->
      <a href="reviews.php" class="back-icon">
        <i class="bi bi-arrow-left"></i>
      </a>

      <!-- Header -->
      <h1>Write a review</h1>

      <!-- Appointment Selection -->
      <div class="form-group">
        <label for="appointmentSelect">Select Appointment:</label>
        <select id="appointmentSelect" class="form-control">
            <option value="">Select an Appointment</option>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo htmlspecialchars($row['service'] . ' on ' . $row['appointment_date']); ?>
                </option>
            <?php endwhile; ?>
        </select>
      </div>

      <!-- Rate Section -->
      <div class="rate">
        <strong>Rate</strong>
        <div class="stars">
          <i class="bi bi-star" data-value="1"></i>
          <i class="bi bi-star" data-value="2"></i>
          <i class="bi bi-star" data-value="3"></i>
          <i class="bi bi-star" data-value="4"></i>
          <i class="bi bi-star" data-value="5"></i>
        </div>
      </div>

      <!-- Textarea -->
      <div class="textarea-container">
        <textarea id="reviewText" placeholder="Share your thoughts about our service"></textarea>
      </div>

      <!-- Buttons -->
      <div class="post-buttons">
        <button class="cancel-button" onclick="window.location.href='reviews.php'">Cancel</button>
        <button class="post-button" id="postReviewButton">Post</button>
      </div>
    </div>

    <script>
        let rating = 0; // Initialize rating variable

        // Add click event listeners to stars
        document.querySelectorAll('.stars i').forEach(star => {
            star.addEventListener('click', function() {
                rating = this.getAttribute('data-value'); // Get the value of the clicked star
                updateStars(rating); // Update the stars display
            });
        });

        function updateStars(rating) {
            const stars = document.querySelectorAll('.stars i');
            stars.forEach(star => {
                if (star.getAttribute('data-value') <= rating) {
                    star.classList.remove('bi-star'); // Remove empty star class
                    star.classList.add('bi-star-fill'); // Add filled star class
                } else {
                    star.classList.remove('bi-star-fill'); // Remove filled star class
                    star.classList.add('bi-star'); // Add empty star class
                }
            });
        }

        document.getElementById('postReviewButton').addEventListener('click', function() {
            const appointment_id = document.getElementById('appointmentSelect').value; // Get the selected appointment ID

            if (!appointment_id) {
                alert('Please select an appointment.');
                return;
            }

            if (rating === 0) {
                alert('Please select a rating.');
                return;
            }

            const reviewText = document.getElementById('reviewText').value;

            // Send the review to the server
            fetch('submit_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    appointment_id: appointment_id, // Include the appointment ID
                    rating: rating,
                    review_text: reviewText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review posted successfully!');
                    window.location.href = 'reviews.php'; // Redirect to reviews page
                } else {
                    alert('Error posting review: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
</body>
</html>