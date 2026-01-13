# SilkSerenity

A comprehensive salon booking and management system designed for eyelash services. SilkSerenity provides an elegant platform for customers to book appointments, browse services, and leave reviews, while offering administrators powerful tools to manage bookings, users, services, and transactions.

## Table of Contents

- [Introduction](#introduction)
- [Key Features](#key-features)
- [Project Structure](#project-structure)
- [Installation Guide](#installation-guide)
- [Usage](#usage)
- [Database Structure](#database-structure)
- [API Endpoints](#api-endpoints)
- [Environment Variables](#environment-variables)
- [Technologies Used](#technologies-used)
- [Contribution Guide](#contribution-guide)
- [Support & Contact](#support--contact)

## Introduction

SilkSerenity is a full-stack web application that streamlines the appointment booking process for a salon specializing in eyelash services. The system features a beautiful, user-friendly interface for customers and a comprehensive admin panel for business management.

The application handles the complete customer journey from registration and service browsing to appointment booking and review submission, while providing administrators with tools for managing all aspects of the business.

## Key Features

### Customer Features
- **User Authentication**: Secure registration and login system
- **Service Browsing**: View available services with descriptions and pricing
- **Appointment Booking**: Book appointments with date/time selection and slot availability checking
- **Account Management**: View and manage personal information and appointment history
- **Reviews System**: Submit reviews and ratings for completed appointments
- **Gallery**: Browse service images and portfolio
- **Contact Form**: Send inquiries and messages to the salon

### Admin Features
- **Dashboard**: Overview of daily appointments, revenue, and key metrics
- **User Management**: View, manage, and delete user accounts with statistics
- **Appointment Management**: View all appointments, update statuses (Pending, Confirmed, Completed, Cancelled), and manage bookings
- **Service Management**: Add, update, and manage service offerings with prices and descriptions
- **Transaction Management**: Track payments, update payment statuses, and manage financial records
- **Analytics & Reports**: Generate sales reports (daily, weekly, monthly, yearly) with CSV export
- **Email Notifications**: Automatic email notifications for new appointments

## Project Structure

```
SilkSerenity/
├── svg/                    # SVG images and icons
├── vendor/                 # Composer dependencies (PHPMailer)
│
├── home.html               # Homepage
├── login.html              # User login page
├── signup.html             # User registration page
├── account.html            # User account management
├── gallery.html            # Service gallery
│
├── services.php            # Services listing page
├── appointments.php        # Appointment booking page
├── reviews.php             # Reviews display page
├── contact.php             # Contact form page
│
├── admin_login.html        # Admin login page
├── admin_dashboard.php     # Admin dashboard
├── admin_users.php         # User management
├── admin_services.php      # Service management
├── admin_transactions.php  # Transaction management
├── admin_analytics.php     # Analytics and reports
├── admin_manage.php        # General admin management
│
├── connection.php          # Database connection configuration
├── authentication.php      # User authentication handler
├── admin_auth.php          # Admin authentication handler
├── register.php            # User registration handler
├── book_appointment.php    # Appointment booking handler
├── submit_review.php       # Review submission handler
├── update_status.php       # Appointment status update handler
├── email_utils.php         # Email notification utilities
│
├── get_userdata.php        # Fetch user data API
├── get_account_data.php    # Fetch account data API
├── get_appointments.php    # Fetch appointments API
├── get_user_appointments.php # Fetch user-specific appointments
├── get_admin_appointments.php # Fetch admin appointments
├── get_booked_slots.php    # Fetch booked time slots
├── get_dashboard_data.php  # Fetch dashboard statistics
│
├── script.js               # Main frontend JavaScript
├── appointments.js         # Appointment booking JavaScript
├── admin_login.js          # Admin login JavaScript
├── admin_dashboard.js      # Admin dashboard JavaScript
│
├── styles.css              # Main stylesheet
├── admin_styles.css        # Admin panel stylesheet
├── mediaqueries.css        # Responsive design styles
│
├── composer.json           # PHP dependencies
├── composer.lock           # Locked dependency versions
│
└── README.md               # This file
```

## Installation Guide

### Prerequisites

- **Web Server**: Apache or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher (or MariaDB equivalent)
- **Composer**: For PHP dependency management
- **XAMPP/WAMP/MAMP**: Recommended for local development

### Step-by-Step Installation

1. **Clone or Download the Project**
   ```bash
   # Extract the project to your web server directory
   # For XAMPP: C:\xampp\htdocs\SilkSerenity
   # For WAMP: C:\wamp64\www\SilkSerenity
   ```

2. **Create the Database**
   ```sql
   CREATE DATABASE users_db;
   ```

3. **Create Database Tables**
   
   Create the following tables in your `users_db` database:

   ```sql
   -- Users table
   CREATE TABLE userdata (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) NOT NULL,
       email VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL
   );

   -- Services table
   CREATE TABLE services (
       id INT AUTO_INCREMENT PRIMARY KEY,
       service_name VARCHAR(255) NOT NULL,
       price DECIMAL(10,2) NOT NULL,
       description TEXT
   );

   -- Appointments table
   CREATE TABLE appointments (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       first_name VARCHAR(255) NOT NULL,
       last_name VARCHAR(255) NOT NULL,
       email VARCHAR(255) NOT NULL,
       phone VARCHAR(20),
       address TEXT,
       age INT,
       service VARCHAR(255) NOT NULL,
       source VARCHAR(255),
       appointment_date DATE NOT NULL,
       appointment_time TIME NOT NULL,
       status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
       FOREIGN KEY (user_id) REFERENCES userdata(id) ON DELETE CASCADE
   );

   -- Transactions table
   CREATE TABLE transactions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       appointment_id INT NOT NULL,
       amount DECIMAL(10,2) NOT NULL,
       payment_method VARCHAR(50),
       payment_status ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
       transaction_date DATE,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
   );

   -- Reviews table
   CREATE TABLE reviews (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       appointment_id INT NOT NULL,
       rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
       review_text TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES userdata(id) ON DELETE CASCADE,
       FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
   );

   -- Admin table
   CREATE TABLE admin (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL
   );
   ```

4. **Configure Database Connection**
   
   Update `connection.php` with your database credentials:
   ```php
   $host = "localhost";
   $dbUsername = "root";
   $dbPassword = "";  // Your MySQL password
   $dbname = "users_db";
   ```

5. **Install PHP Dependencies**
   ```bash
   composer install
   ```
   
   This will install PHPMailer for email functionality.

6. **Populate Services**
   
   Navigate to `populate_services.php` in your browser to populate initial service data:
   ```
   http://localhost/SilkSerenity/populate_services.php
   ```

7. **Configure Email Settings** (Optional)
   
   Update `email_utils.php` with your SMTP settings for email notifications:
   ```php
   // Configure SMTP settings
   $mail->Host = 'smtp.gmail.com';
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'your-app-password';
   ```

8. **Set Up Admin Account**
   
   Insert an admin account directly into the database:
   ```sql
   INSERT INTO admin (username, password) VALUES ('admin', 'your-password');
   ```

9. **Access the Application**
   ```
   User Interface: http://localhost/SilkSerenity/home.html
   Admin Panel: http://localhost/SilkSerenity/admin_login.html
   ```

## Usage

### For Customers

1. **Registration**: Create an account by visiting the signup page
2. **Browse Services**: View available services and pricing on the Services page
3. **Book Appointment**: Select a service, choose date and time, and fill in appointment details
4. **View Appointments**: Check your appointment history in the Account section
5. **Submit Reviews**: Leave reviews for completed appointments
6. **Contact**: Use the contact form for inquiries

### For Administrators

1. **Login**: Access the admin panel using admin credentials
2. **Dashboard**: View key metrics, today's appointments, and revenue statistics
3. **Manage Appointments**: Update appointment statuses, view all bookings
4. **Manage Users**: View user statistics, manage user accounts
5. **Manage Services**: Update service prices and descriptions
6. **Transactions**: Record payments and update payment statuses
7. **Reports**: Generate and download sales reports in CSV format
8. **Analytics**: View detailed analytics and revenue breakdowns

## Database Structure

### Tables Overview

| Table Name | Purpose | Key Relationships |
|------------|---------|-------------------|
| `userdata` | Stores customer account information | Referenced by `appointments` and `reviews` |
| `services` | Stores available salon services | Referenced by `appointments` (via service name) |
| `appointments` | Stores booking information | Links to `userdata`, referenced by `transactions` and `reviews` |
| `transactions` | Stores payment records | Links to `appointments` |
| `reviews` | Stores customer reviews and ratings | Links to `userdata` and `appointments` |
| `admin` | Stores administrator accounts | Independent table |

### Table Relationships

```
userdata (1) ────< (many) appointments
appointments (1) ────< (many) transactions
appointments (1) ────< (many) reviews
userdata (1) ────< (many) reviews
```

### Key Fields

- **userdata**: `id`, `username`, `email`, `password`
- **services**: `id`, `service_name`, `price`, `description`
- **appointments**: `id`, `user_id`, `service`, `appointment_date`, `appointment_time`, `status`
- **transactions**: `id`, `appointment_id`, `amount`, `payment_status`, `transaction_date`
- **reviews**: `id`, `user_id`, `appointment_id`, `rating`, `review_text`

## API Endpoints

### Authentication
- `POST /authentication.php` - User login
- `POST /register.php` - User registration
- `POST /admin_auth.php` - Admin login

### User Data
- `GET /get_userdata.php` - Get user information
- `GET /get_account_data.php` - Get account data with appointments
- `GET /get_user_appointments.php` - Get user's appointments

### Appointments
- `POST /book_appointment.php` - Create new appointment
- `GET /get_appointments.php` - Get all appointments
- `GET /get_admin_appointments.php` - Get appointments for admin
- `GET /get_booked_slots.php` - Get booked time slots
- `POST /update_status.php` - Update appointment status

### Reviews
- `POST /submit_review.php` - Submit a review
- `GET /reviews.php` - Display reviews

### Admin
- `GET /get_dashboard_data.php` - Get dashboard statistics
- `POST /admin_transactions.php` - Manage transactions
- `POST /admin_services.php` - Manage services
- `POST /admin_users.php` - Manage users

## Environment Variables

The application uses a simple PHP configuration file (`connection.php`) instead of environment variables. Update the following values:

```php
$host = "localhost";           // Database host
$dbUsername = "root";          // Database username
$dbPassword = "";              // Database password
$dbname = "users_db";          // Database name
```

For email configuration, update `email_utils.php`:
```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->setFrom('your-email@gmail.com', 'SilkSerenity');
```

## Technologies Used

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL** - Database management
- **PHPMailer** - Email functionality

### Frontend
- **HTML5** - Markup
- **CSS3** - Styling
- **JavaScript** - Client-side interactivity
- **Bootstrap 5.3** - UI framework
- **Bootstrap Icons** - Icon library
- **Google Fonts (Playfair Display)** - Typography

### Development Tools
- **Composer** - PHP dependency management

## Contribution Guide

Contributions are welcome! To contribute to this project:

1. **Fork the Repository**: Create your own copy of the project
2. **Create a Branch**: Make a new branch for your feature (`git checkout -b feature/AmazingFeature`)
3. **Make Changes**: Implement your improvements or fixes
4. **Test Thoroughly**: Ensure all functionality works correctly
5. **Commit Changes**: Write clear commit messages
6. **Push to Branch**: Upload your changes (`git push origin feature/AmazingFeature`)
7. **Open Pull Request**: Submit your changes for review

### Coding Standards
- Follow PHP PSR standards
- Use meaningful variable and function names
- Add comments for complex logic
- Maintain consistent indentation (4 spaces)
- Test all features before submitting

## Support & Contact

For support, questions, or inquiries about SilkSerenity:

- **Project Repository**: [GitHub Repository URL]
- **Issues**: Report bugs or request features through the issue tracker
- **Email**: [Contact Email]

---

**Note**: This is a development version. For production deployment, ensure:
- Secure password hashing (currently uses plain text - **NOT RECOMMENDED FOR PRODUCTION**)
- HTTPS encryption
- Input validation and sanitization
- SQL injection prevention (prepared statements are used)
- Session security
- Error handling and logging

---

© 2024 SilkSerenity. All rights reserved.
