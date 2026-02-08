<?php
require_once 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse(false, 'Method not allowed');
}

// Get JSON input
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') == 0) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Sanitize
$data = sanitizeInput($input);

// Validate required fields
$required = ['name', 'email', 'phone', 'service_type', 'preferred_date'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        jsonResponse(false, 'Please fill in all required fields');
    }
}

if (!isValidEmail($data['email'])) {
    jsonResponse(false, 'Invalid email address');
}

try {
    // 1. Log
    logActivity('Booking Request', "Client: {$data['name']}, Service: {$data['service_type']}, Date: {$data['preferred_date']}");

    // 2. Email Admin
    $adminSubject = "New Booking Request: {$data['service_type']}";
    $adminBody = "
        <h2>New Booking Request</h2>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Name:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>{$data['name']}</td></tr>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Email:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>{$data['email']}</td></tr>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Phone:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>{$data['phone']}</td></tr>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Service:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>{$data['service_type']}</td></tr>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Date:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>{$data['preferred_date']}</td></tr>
            <tr><td style='padding: 5px; border-bottom: 1px solid #eee;'><strong>Time:</strong></td><td style='padding: 5px; border-bottom: 1px solid #eee;'>".($data['preferred_time'] ?? 'Any')."</td></tr>
        </table>
        <p><strong>Notes:</strong></p>
        <p>".($data['notes'] ?? 'None')."</p>
    ";

    sendEmail(ADMIN_EMAIL, $adminSubject, $adminBody);

    // 3. Email User
    $userSubject = "Booking Received - " . SITE_NAME;
    $userBody = file_get_contents('../utils/email-templates/booking-confirmation.html');
    
    sendEmail($data['email'], $userSubject, $userBody);

    jsonResponse(true, 'Your booking request has been received! We will confirm your appointment shortly.');

} catch (Exception $e) {
    logActivity('Error', 'Booking form error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again or contact us directly.');
}
?>
