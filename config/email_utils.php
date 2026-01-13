<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class EmailUtils {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'silkserenity@gmail.com'; // Replace with your Gmail
            $mail->Password = 'Password'; // Replace with your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Default sender
            $mail->setFrom('silkserenity@gmail.com', 'SilkSerenity');
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return null;
        }
    }

    public static function sendAppointmentNotificationToAdmin($appointmentDetails) {
        $mail = self::getMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress('silkserenity@gmail.com'); // Admin email
            $mail->isHTML(true);
            $mail->Subject = 'New Appointment Booking';
            
            // Create HTML body
            $body = "
                <h2>New Appointment Booking</h2>
                <p><strong>Customer Name:</strong> {$appointmentDetails['first_name']} {$appointmentDetails['last_name']}</p>
                <p><strong>Service:</strong> {$appointmentDetails['service']}</p>
                <p><strong>Date:</strong> {$appointmentDetails['appointment_date']}</p>
                <p><strong>Time:</strong> {$appointmentDetails['appointment_time']}</p>
                <p><strong>Contact:</strong> {$appointmentDetails['phone']}</p>
                <p><strong>Email:</strong> {$appointmentDetails['email']}</p>
                <p><strong>Address:</strong> {$appointmentDetails['address']}</p>
                <p><strong>Age:</strong> {$appointmentDetails['age']}</p>
            ";
            
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }

    public static function sendStatusUpdateToCustomer($appointmentDetails, $status) {
        $mail = self::getMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($appointmentDetails['email']);
            $mail->isHTML(true);
            
            if ($status === 'Confirmed') {
                $mail->Subject = 'Appointment Confirmed - SilkSerenity';
                $body = "
                    <h2>Your Appointment is Confirmed!</h2>
                    <p>Dear {$appointmentDetails['first_name']},</p>
                    <p>Your appointment has been confirmed. Here are the details:</p>
                    <p><strong>Service:</strong> {$appointmentDetails['service']}</p>
                    <p><strong>Date:</strong> {$appointmentDetails['appointment_date']}</p>
                    <p><strong>Time:</strong> {$appointmentDetails['appointment_time']}</p>
                    <p><strong>Price:</strong> ₱{$appointmentDetails['price']}</p>
                    <br>
                    <p>Please arrive 30 minutes before your scheduled appointment.</p>
                    <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
                ";
            } else if ($status === 'Cancelled') {
                $mail->Subject = 'Appointment Cancelled - SilkSerenity';
                $body = "
                    <h2>Appointment Cancellation Notice</h2>
                    <p>Dear {$appointmentDetails['first_name']},</p>
                    <p>Your appointment scheduled for {$appointmentDetails['appointment_date']} at {$appointmentDetails['appointment_time']} has been cancelled.</p>
                    <p>If you would like to reschedule, please visit our website or contact us directly.</p>
                ";
            }
            
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return false;
        }
    }
}
?>