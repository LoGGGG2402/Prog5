<?php
// Keep session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// User roles and authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Input and output functions
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

function showError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

function showSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

// Navigation function
function redirect($url) {
    header("Location: $url");
    exit();
}

// UI Helper functions
function isActivePage($url, $path) {
    if (strpos($url, $path) !== false) {
        return 'active';
    }
    return '';
}

// Date formatting function
function formatDate($dateString, $format = 'M j, Y g:i A') {
    return date($format, strtotime($dateString));
}

/**
 * Generate a UUID v4
 * @return string UUID string
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Note: Legacy file handling functions have been removed
// For file uploads, use: FileHandler::uploadFile($file, $targetDir, $allowedTypes)
// For serving files, use: FileHandler::serveFileDownload($filePath, $fileName)
?>