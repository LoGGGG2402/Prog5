<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is a teacher
function isTeacher() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
}

// Check if user is a student
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Display error message
function showError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

// Display success message
function showSuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

// Redirect to URL
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get user by ID
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Upload file
function uploadFile($file, $targetDir, $allowedTypes = []) {
    // Check if target directory exists, create if it doesn't
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            return ['error' => 'Failed to create upload directory.'];
        }
    }
    
    // Make sure directory is writable
    if (!is_writable($targetDir)) {
        chmod($targetDir, 0777);
        if (!is_writable($targetDir)) {
            return ['error' => 'Upload directory is not writable. Please check permissions.'];
        }
    }

    if ($file['error'] != 0) {
        return ['error' => 'File upload error: ' . getFileUploadErrorMessage($file['error'])];
    }

    // Check file size (limit to 10MB)
    $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
    if ($file['size'] > $maxFileSize) {
        return ['error' => 'File is too large. Maximum file size is 10MB.'];
    }

    $fileName = basename($file['name']);
    $targetFile = $targetDir . uniqid() . '_' . $fileName;
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Check if file type is allowed by extension
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        return ['error' => 'Only ' . implode(', ', $allowedTypes) . ' files are allowed.'];
    }

    // Additional MIME type verification
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    // Map of allowed extensions to MIME types
    $allowedMimeTypes = [
        'txt' => ['text/plain', 'text/x-c', 'text/x-c++'],  // Some text files may be detected as source code
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'jpg' => ['image/jpeg', 'image/jpg'],
        'jpeg' => ['image/jpeg', 'image/jpg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'zip' => ['application/zip', 'application/x-zip-compressed']
    ];
    
    // Skip MIME check if the extension isn't in our map
    if (!empty($allowedTypes) && in_array($fileType, array_keys($allowedMimeTypes))) {
        $validMime = false;
        foreach ($allowedMimeTypes[$fileType] as $mime) {
            if (strpos($mimeType, $mime) !== false) {
                $validMime = true;
                break;
            }
        }
        
        if (!$validMime) {
            return ['error' => 'The file content does not match its extension. This might be a security risk.'];
        }
    }

    // Upload the file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'path' => $targetFile, 'filename' => $fileName];
    } else {
        return ['error' => 'Failed to upload file. Check directory permissions.'];
    }
}

// Function to get meaningful error messages for file upload errors
function getFileUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk";
        case UPLOAD_ERR_EXTENSION:
            return "File upload stopped by extension";
        default:
            return "Unknown upload error";
    }
}

// Get all users
function getAllUsers() {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT id, username, fullname, email, phone, role, avatar FROM users ORDER BY role DESC, fullname ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

// Get recent messages for a user
function getRecentMessages($userId, $limit = 10) {
    global $conn;
    $userId = (int) $userId;
    $limit = (int) $limit;
    
    // Get the most recent messages where the user is the receiver and they are unread
    $stmt = mysqli_prepare($conn, "
        SELECT m.*, u.fullname, u.avatar, u.username, u.role
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.receiver_id = ? AND m.is_read = 0
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    mysqli_stmt_bind_param($stmt, "ii", $userId, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    
    return $messages;
}

// Mark message as read
function markMessageAsRead($messageId) {
    global $conn;
    $messageId = (int) $messageId;
    
    $stmt = mysqli_prepare($conn, "UPDATE messages SET is_read = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $messageId);
    return mysqli_stmt_execute($stmt);
}

// Mark all messages from a sender as read
function markMessagesAsRead($receiverId, $senderId) {
    global $conn;
    $receiverId = (int) $receiverId;
    $senderId = (int) $senderId;
    
    $stmt = mysqli_prepare($conn, "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    mysqli_stmt_bind_param($stmt, "ii", $senderId, $receiverId);
    return mysqli_stmt_execute($stmt);
}
?>
