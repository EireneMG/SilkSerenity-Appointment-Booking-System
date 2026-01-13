<?php
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";  // default XAMPP password is blank
$dbname = "users_db";

// Create connection
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>