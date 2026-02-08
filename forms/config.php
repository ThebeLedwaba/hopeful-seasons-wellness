<?php
// Database configuration (for future use)
define('DB_HOST', 'localhost');
define('DB_NAME', 'hopeful_seasons_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Email configuration
define('SITE_EMAIL', 'contact@hopefulseasonswellness.co.za');
define('ADMIN_EMAIL', 'admin@hopefulseasonswellness.co.za');
define('EMAIL_SUBJECT_PREFIX', '[Hopeful Seasons] ');

// Site configuration
define('SITE_NAME', 'Hopeful Seasons Wellness');
define('SITE_URL', 'https://www.hopefulseasonswellness.co.za');

// Security
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
define('RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY'); // Get from Google

// Booking settings
define('MIN_BOOKING_HOURS', 24); // Minimum hours before appointment
define('MAX_BOOKING_DAYS', 90); // Maximum days in advance
define('SESSION_DURATION', 50); // Session duration in minutes
define('BREAK_DURATION', 10); // Break between sessions in minutes

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON responses
header('Content-Type: application/json; charset=utf-8');

// CORS headers (adjust for production)
header('Access-Control-Allow-Origin: ' . SITE_URL);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400'); // 24 hours

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to generate response
function jsonResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    exit();
}

// Validate reCAPTCHA (if enabled)
function validateRecaptcha($token) {
    if (empty(RECAPTCHA_SECRET_KEY)) {
        return true; // Skip if no secret key
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result);
    
    return $response->success;
}

// Send email function
function sendEmail($to, $subject, $body, $headers = null) {
    if (!$headers) {
        $headers = [
            'From' => SITE_EMAIL,
            'Reply-To' => SITE_EMAIL,
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        // Build headers string
        $headersString = '';
        foreach ($headers as $key => $value) {
            $headersString .= "$key: $value\r\n";
        }
    }
    
    $fullSubject = EMAIL_SUBJECT_PREFIX . $subject;
    
    return mail($to, $fullSubject, $body, $headersString);
}

// Generate CSRF token
function generateCsrfToken() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCsrfToken($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Log activity (for debugging and security)
function logActivity($action, $details = '') {
    $logFile = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';
    
    $logEntry = "[$timestamp] [$ip] [$userAgent] $action";
    if ($details) {
        $logEntry .= " - $details";
    }
    $logEntry .= PHP_EOL;
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?>