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

/**
 * Database functions - optimized versions
 */

/**
 * Execute a prepared query with parameters and return result
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (i, s, d, b)
 * @param array $params Array of parameters
 * @param bool $single Whether to return single row or all rows
 * @return array|null Query result
 */
function dbQuery($sql, $types = "", $params = [], $single = false) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("MySQL prepare error: " . mysqli_error($conn));
        return null;
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("MySQL execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        mysqli_stmt_close($stmt);
        return null;
    }
    
    if ($single) {
        $data = mysqli_fetch_assoc($result);
    } else {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    mysqli_stmt_close($stmt);
    return $data;
}

/**
 * Execute an INSERT, UPDATE or DELETE query and return affected rows or insert ID
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (i, s, d, b)
 * @param array $params Array of parameters
 * @param bool $getInsertId Whether to return insert ID instead of affected rows
 * @return int|bool Number of affected rows, insert ID, or false on failure
 */
function dbExecute($sql, $types = "", $params = [], $getInsertId = false) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("MySQL prepare error: " . mysqli_error($conn));
        return false;
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("MySQL execute error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = $getInsertId ? mysqli_insert_id($conn) : mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// Get user by ID
function getUserById($id) {
    return dbQuery("SELECT * FROM users WHERE id = ?", "i", [$id], true);
}

// Get all users
function getAllUsers() {
    return dbQuery("SELECT * FROM users ORDER BY role, fullname");
}

/**
 * File handling functions
 */

/**
 * Upload file with better error handling
 * @param array $file The $_FILES array element
 * @param string $targetDir The target directory
 * @param array $allowedTypes Allowed file extensions
 * @return array Result with success/error status
 */
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
        $errorMessages = [
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload'
        ];
        
        $errorCode = $file['error'];
        $errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Unknown upload error';
        
        return ['error' => $errorMessage];
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file extension
    if (!empty($allowedTypes) && !in_array($fileExt, $allowedTypes)) {
        return ['error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    // Generate unique filename to prevent overwrites
    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . '/' . $filename;
    
    // Move the file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['error' => 'Failed to upload file. Please try again.'];
    }
    
    // Return success with path
    return [
        'success' => true,
        'path' => $targetPath,
        'filename' => $filename
    ];
}

/**
 * Get avatar URL for a user
 * @param string|null $avatar Avatar path/URL
 * @return string Avatar URL
 */
function getAvatarUrl($avatar) {
    if (!empty($avatar)) {
        return $avatar;
    }
    return 'img/default-avatar.png';
}

/**
 * Handle avatar upload or URL
 * @param array $formData POST data containing avatar_url
 * @param array $files FILES array
 * @return array Result with avatar path or error
 */
function handleAvatarUpdate($formData, $files) {
    // Handle avatar upload or URL
    if (isset($files['avatar']) && $files['avatar']['error'] == 0) {
        // Create avatar directory if it doesn't exist
        if (!file_exists(AVATAR_DIR)) {
            mkdir(AVATAR_DIR, 0777, true);
        }

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadResult = uploadFile($files['avatar'], AVATAR_DIR, $allowedTypes);

        if (isset($uploadResult['success'])) {
            // Convert file path to URL format
            $avatarPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $uploadResult['path']);
            // Add site URL if it's not an absolute path
            if (substr($avatarPath, 0, 1) === '/') {
                $avatarPath = SITE_URL . $avatarPath;
            }
            return ['success' => true, 'avatar' => $avatarPath];
        } else {
            return ['error' => $uploadResult['error']];
        }
    } elseif (isset($formData['avatar_url']) && !empty($formData['avatar_url'])) {
        $avatarUrl = sanitize($formData['avatar_url']);
        return ['success' => true, 'avatar' => $avatarUrl];
    }
    
    return ['success' => false]; // No changes to avatar
}

/**
 * Get MIME type for a file
 * @param string $filename Filename
 * @return string MIME type
 */
function getMimeType($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'zip' => 'application/zip',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

/**
 * Serve a file for download
 * @param string $filePath File path
 * @param string $fileName Filename
 */
function serveFileDownload($filePath, $fileName) {
    if (!file_exists($filePath)) {
        header('HTTP/1.0 404 Not Found');
        echo "File not found";
        exit;
    }
    
    $mimeType = getMimeType($fileName);
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache');
    
    readfile($filePath);
    exit;
}

/**
 * Assignment and submission functions
 */

/**
 * Get assignments for a specific teacher or all assignments
 * @param int|null $teacherId Teacher ID (null for all assignments)
 * @return array Assignments
 */
function getAssignments($teacherId = null) {
    if ($teacherId !== null) {
        return dbQuery(
            "SELECT assignments.*, users.fullname AS teacher_name 
             FROM assignments JOIN users ON assignments.teacher_id = users.id 
             WHERE assignments.teacher_id = ? 
             ORDER BY assignments.created_at DESC",
            "i", [$teacherId]
        );
    } else {
        return dbQuery(
            "SELECT assignments.*, users.fullname AS teacher_name 
             FROM assignments JOIN users ON assignments.teacher_id = users.id 
             ORDER BY assignments.created_at DESC"
        );
    }
}

/**
 * Get submissions with filter options
 * @param array $filters Associative array of filters (assignment_id, student_id)
 * @return array Submissions with details
 */
function getSubmissions($filters = []) {
    $sql = "SELECT submissions.*, 
            users.fullname AS student_name, users.username, users.avatar,
            assignments.title AS assignment_title 
            FROM submissions 
            JOIN users ON submissions.student_id = users.id 
            JOIN assignments ON submissions.assignment_id = assignments.id 
            WHERE 1=1";
    
    $types = "";
    $params = [];
    
    if (!empty($filters['assignment_id'])) {
        $sql .= " AND submissions.assignment_id = ?";
        $types .= "i";
        $params[] = $filters['assignment_id'];
    }
    
    if (!empty($filters['student_id'])) {
        $sql .= " AND submissions.student_id = ?";
        $types .= "i";
        $params[] = $filters['student_id'];
    }
    
    $sql .= " ORDER BY submissions.created_at DESC";
    
    return dbQuery($sql, $types, $params);
}

/**
 * Get a single submission by ID
 * @param int $submissionId Submission ID
 * @return array|null Submission details or null if not found
 */
function getSubmissionById($submissionId) {
    return dbQuery(
        "SELECT submissions.*, 
         users.fullname AS student_name, users.username, 
         assignments.title AS assignment_title 
         FROM submissions 
         JOIN users ON submissions.student_id = users.id 
         JOIN assignments ON submissions.assignment_id = assignments.id 
         WHERE submissions.id = ?",
        "i", [$submissionId], true
    );
}

/**
 * Create or update a submission
 * @param array $data Submission data
 * @return array Result with success/error status
 */
function saveSubmission($data) {
    // Check if submission exists
    $existing = dbQuery(
        "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?", 
        "ii", [$data['assignment_id'], $data['student_id']], 
        true
    );
    
    if ($existing) {
        // Update existing submission
        $result = dbExecute(
            "UPDATE submissions 
             SET file_path = ?, filename = ?, created_at = CURRENT_TIMESTAMP 
             WHERE id = ?",
            "ssi", [$data['file_path'], $data['filename'], $existing['id']]
        );
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => 'Your submission has been updated!',
                'id' => $existing['id']
            ];
        }
    } else {
        // Create new submission
        $id = dbExecute(
            "INSERT INTO submissions (assignment_id, student_id, file_path, filename) 
             VALUES (?, ?, ?, ?)",
            "iiss", 
            [$data['assignment_id'], $data['student_id'], $data['file_path'], $data['filename']],
            true
        );
        
        if ($id) {
            return [
                'success' => true,
                'message' => 'Your submission has been received!',
                'id' => $id
            ];
        }
    }
    
    return [
        'success' => false,
        'error' => 'Database error: Unable to save submission'
    ];
}

/**
 * Get file by type and ID
 * @param string $type File type (assignment, submission, challenge)
 * @param int $id Item ID
 * @return array|null File information or null if not found
 */
function getFileByTypeAndId($type, $id) {
    $table = '';
    $additionalCheck = '';
    $additionalParams = [];
    
    switch ($type) {
        case 'assignment':
            $table = 'assignments';
            break;
        case 'submission':
            $table = 'submissions';
            // For submissions, students can only access their own submissions
            if (isStudent()) {
                $additionalCheck = ' AND student_id = ?';
                $additionalParams = [$_SESSION['user_id']];
            }
            break;
        case 'challenge':
            $table = 'challenges';
            break;
        default:
            return null;
    }
    
    $params = array_merge([$id], $additionalParams);
    $types = 'i' . str_repeat('i', count($additionalParams));
    
    return dbQuery(
        "SELECT file_path, filename FROM $table WHERE id = ?" . $additionalCheck,
        $types,
        $params,
        true
    );
}

/**
 * Check if student has submitted an assignment
 * @param int $assignmentId Assignment ID
 * @param int $studentId Student ID
 * @return bool Whether the student has submitted the assignment
 */
function hasSubmittedAssignment($assignmentId, $studentId) {
    $result = dbQuery(
        "SELECT COUNT(*) as count FROM submissions WHERE assignment_id = ? AND student_id = ?",
        "ii",
        [$assignmentId, $studentId],
        true
    );
    
    return $result && $result['count'] > 0;
}

/**
 * Add submission status to assignments for a student
 * @param array $assignments Array of assignments
 * @param int $studentId Student ID
 * @return array Assignments with submission status
 */
function addSubmissionStatusToAssignments($assignments, $studentId) {
    foreach ($assignments as &$assignment) {
        $assignment['has_submitted'] = hasSubmittedAssignment($assignment['id'], $studentId);
    }
    return $assignments;
}

/**
 * Utility function to make UI related code simpler
 * @param string|null $url URL to check
 * @param string $path Path to match
 * @return string CSS class if matched
 */
function isActivePage($url, $path) {
    if (strpos($url, $path) !== false) {
        return 'active';
    }
    return '';
}

/**
 * Function to format date in a consistent way
 * @param string $dateString Date string
 * @param string $format Format string (default: 'M j, Y g:i A')
 * @return string Formatted date
 */
function formatDate($dateString, $format = 'M j, Y g:i A') {
    return date($format, strtotime($dateString));
}
?>
