<?php
require_once 'includes/init.php';
require_once 'utils/FileHandler.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    echo "Access denied";
    exit;
}

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Validate parameters
if (empty($type) || empty($id)) {
    header('HTTP/1.0 400 Bad Request');
    echo "Invalid request";
    exit;
}

// Get file information based on type
$fileInfo = null;

switch ($type) {
    case 'assignment':
        $fileInfo = $assignmentModel->find($id);
        break;
    case 'submission':
        $fileInfo = $submissionModel->find($id);
        break;
    case 'challenge':
        $fileInfo = $challengeModel->find($id);
        break;
    default:
        header('HTTP/1.0 400 Bad Request');
        echo "Invalid file type";
        exit;
}

// Check if file exists
if (!$fileInfo || empty($fileInfo['file_path']) || !file_exists($fileInfo['file_path'])) {
    header('HTTP/1.0 404 Not Found');
    echo "File not found";
    exit;
}

// Serve the file for download using the FileHandler class
FileHandler::serveFileDownload($fileInfo['file_path'], $fileInfo['filename'] ?? basename($fileInfo['file_path']));
?>
