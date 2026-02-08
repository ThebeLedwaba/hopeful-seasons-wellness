<?php
require_once 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse(false, 'Method not allowed');
}

// Get JSON input if content type is application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') == 0) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

// Sanitize input
$data = sanitizeInput($input);

// Validate required fields
$required = ['name', 'email', 'message'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        jsonResponse(false, 'All required fields must be filled');
    }
}

// Validate email
if (!isValidEmail($data['email'])) {
    jsonResponse(false, 'Invalid email address');
}

try {
    // 1. Log the activity
    logActivity('Contact Form Submission', "From: {$data['name']} ({$data['email']})");

    // 2. Send Email to Admin
    $adminSubject = "New Contact Inquiry from {$data['name']}";
    $adminBody = "
        <h2>New Contact Inquiry</h2>
        <p><strong>Name:</strong> {$data['name']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>
        <p><strong>Phone:</strong> " . ($data['phone'] ?? 'Not provided') . "</p>
        <p><strong>Message:</strong></p>
        <blockquote style='background: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;'>
            " . nl2br($data['message']) . "
        </blockquote>
    ";
    
    // Send to Admin
    sendEmail(ADMIN_EMAIL, $adminSubject, $adminBody);

    // 3. Send Auto-reply to User
    $userSubject = "We received your message - " . SITE_NAME;
    $userBody = file_get_contents('../utils/email-templates/contact-received.html');
    
    // simple template replacement if needed, or use the static file
    // For now we just send the static HTML file content
    
    sendEmail($data['email'], $userSubject, $userBody);

    // Success response
    jsonResponse(true, 'Thank you! Your message has been sent successfully.');

} catch (Exception $e) {
    logActivity('Error', 'Contact form error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred while sending your message. Please try again later.');
}
?>
