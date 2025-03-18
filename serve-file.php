<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    echo "Access denied";
    exit;
}

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate parameters
if (empty($type) || $id <= 0) {
    header('HTTP/1.0 400 Bad Request');
    echo "Invalid request";
    exit;
}

// Get file information using the consolidated function
$fileInfo = getFileByTypeAndId($type, $id);

// Check if file exists
if (!$fileInfo || empty($fileInfo['file_path']) || !file_exists($fileInfo['file_path'])) {
    header('HTTP/1.0 404 Not Found');
    echo "File not found";
    exit;
}

// Serve the file for download
serveFileDownload($fileInfo['file_path'], $fileInfo['filename']);
?>
