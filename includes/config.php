<?php
// Database configuration - edit these values for your environment
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sms_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Branding Settings
define('SCHOOL_NAME', 'Modern School & Coaching');
define('SCHOOL_ADDR', 'Education Colony, Phase 2, Karachi, Pakistan');
define('SCHOOL_CONTACT', '+92 300 0000000');
define('SCHOOL_EMAIL', 'info@school.pk');

// Session settings
ini_set('session.use_strict_mode', 1);
session_start();
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Base URL (adjust if deployed to a subfolder)
define('BASE_URL', '/SMS/');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
