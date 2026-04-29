<?php
// VULNERABILITY: Hardcoded credentials (ASVS 4.2.2, 14.1.1)
// VULNERABILITY: No encryption of sensitive configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Default XAMPP MySQL user
define('DB_PASS', '');          // Default XAMPP has empty password
define('DB_NAME', 'student_portal');

// VULNERABILITY: Weak session configuration (ASVS 7.1.1, 7.1.2)
// No session.cookie_httponly
// No session.cookie_secure
// No session.cookie_samesite
session_start();

// VULNERABILITY: Debug mode enabled (ASVS 7.2.1)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// VULNERABILITY: Weak session secret (not using proper session regeneration)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Database connection function
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// VULNERABILITY: Weak password hashing (ASVS 6.4.1)
// Using MD5 instead of bcrypt/Argon2
function hashPassword($password) {
    return md5($password);  // CRYPTographically broken!
}

// VULNERABILITY: No rate limiting function (ASVS 6.3.1)
function checkRateLimit($username, $ip) {
    // Intentionally empty - no rate limiting implemented
    return true;
}

// VULNERABILITY: Verbose error messages (ASVS 7.2.1)
function showError($message) {
    return "<div class='alert alert-error'>Error: " . $message . "</div>";
}
?>