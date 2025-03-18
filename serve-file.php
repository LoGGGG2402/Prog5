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

$filePath = '';
$fileName = '';

// Get the appropriate file based on type
if ($type === 'assignment') {
    // Get assignment file
    $stmt = mysqli_prepare($conn, "SELECT file_path, filename FROM assignments WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $filePath = $row['file_path'];
        $fileName = $row['filename'];
    }
} elseif ($type === 'submission') {
    // Get submission file - Check permissions
    $stmt = mysqli_prepare($conn, 
        "SELECT s.file_path, s.filename, s.student_id 
         FROM submissions s 
         WHERE s.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Only allow teachers or the student who submitted it
        if (isTeacher() || $_SESSION['user_id'] == $row['student_id']) {
            $filePath = $row['file_path'];
            $fileName = $row['filename'];
        } else {
            header('HTTP/1.0 403 Forbidden');
            echo "You don't have permission to access this file";
            exit;
        }
    }
} elseif ($type === 'challenge') {
    // Get challenge file
    $stmt = mysqli_prepare($conn, "SELECT file_path, result FROM challenges WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $filePath = $row['file_path'];
        $fileName = basename($filePath);
        
        // Allow download if user is a teacher or if student has solved the challenge
        if (isTeacher()) {
            // Teachers can always download
        } elseif (isStudent()) {
            // For students, check if they've provided the correct answer
            $hasAccess = false;
            
            // Get the correct answer for this challenge
            $correctResult = $row['result'];
            
            // Check if student session has the correct answer
            if (isset($_SESSION['answered_challenges']) && 
                is_array($_SESSION['answered_challenges']) && 
                in_array($id, $_SESSION['answered_challenges'])) {
                $hasAccess = true;
            }
            
            // If not in session, check if the page is being loaded from a challenge submission
            // This is a fallback for when the student has just answered correctly
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if (!$hasAccess && strpos($referer, 'challenges.php') !== false) {
                // We'll allow the access since they're coming from the challenges page
                // This is a simplification - ideally you'd store correct answers in the database
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                header('HTTP/1.0 403 Forbidden');
                echo "You must solve the challenge first to download the file";
                exit;
            }
        } else {
            header('HTTP/1.0 403 Forbidden');
            echo "You don't have permission to access this file";
            exit;
        }
    }
}

// Check if file exists
if (empty($filePath) || !file_exists($filePath)) {
    header('HTTP/1.0 404 Not Found');
    echo "File not found";
    exit;
}

// Set appropriate headers
$mimeType = getMimeType($filePath);
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');

// Output file content
readfile($filePath);
exit;

// Helper function to determine MIME type
function getMimeType($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'zip' => 'application/zip'
    ];
    
    if (isset($mimeTypes[$extension])) {
        return $mimeTypes[$extension];
    }
    
    return 'application/octet-stream';
}
?>
