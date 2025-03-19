<?php
// Database configuration
define('DB_HOST', '127.0.0.1');  // Using IP instead of 'localhost'
define('DB_USER', 'prog5user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'classroom_management');

// Site configuration
define('SITE_URL', 'http://localhost:8000');  // Updated to match your PHP server

// Upload directories - ensure these use absolute paths
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('AVATAR_DIR', UPLOAD_DIR . 'avatars/');
define('ASSIGNMENT_DIR', UPLOAD_DIR . 'assignments/');
define('SUBMISSION_DIR', UPLOAD_DIR . 'submissions/');
define('CHALLENGE_DIR', UPLOAD_DIR . 'challenges/');
define('IMG_DIR', $_SERVER['DOCUMENT_ROOT'] . '/img/');

// URL paths for uploads
define('AVATAR_URL', SITE_URL . '/uploads/avatars/');
define('ASSIGNMENT_URL', SITE_URL . '/uploads/assignments/');
define('SUBMISSION_URL', SITE_URL . '/uploads/submissions/');
define('CHALLENGE_URL', SITE_URL . '/uploads/challenges/');
define('IMG_URL', SITE_URL . '/img/');

// Create upload directories if they don't exist
$directories = [UPLOAD_DIR, AVATAR_DIR, ASSIGNMENT_DIR, SUBMISSION_DIR, CHALLENGE_DIR, IMG_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        // Try to create with full permissions
        if (!@mkdir($dir, 0777, true)) {
            // Log error if directory creation fails
            error_log("Failed to create directory: $dir");
        } else {
            // Ensure directory has proper permissions
            @chmod($dir, 0777);
        }
    }
}

// Create default avatar if it doesn't exist - but safely check GD library first
$defaultAvatarPath = IMG_DIR . 'default-avatar.png';
if (!file_exists($defaultAvatarPath)) {
    // Create a simple text file as placeholder if we can't create an image
    $placeholderContent = "DEFAULT AVATAR PLACEHOLDER";
    @file_put_contents($defaultAvatarPath . '.txt', $placeholderContent);
    
    // Only attempt image creation if GD is available
    if (extension_loaded('gd') && function_exists('imagecreate')) {
        try {
            $width = 200;
            $height = 200;
            $image = @imagecreate($width, $height);
            
            if ($image) {
                // Create colors
                $bgColor = imagecolorallocate($image, 240, 240, 240);
                $textColor = imagecolorallocate($image, 100, 100, 100);
                
                // Add text
                $text = 'User';
                $fontSize = 5;
                $fontWidth = imagefontwidth($fontSize);
                $fontHeight = imagefontheight($fontSize);
                $textWidth = $fontWidth * strlen($text);
                $textHeight = $fontHeight;
                
                // Center the text
                $centerX = ceil(($width - $textWidth) / 2);
                $centerY = ceil(($height - $textHeight) / 2);
                
                // Draw text
                imagestring($image, $fontSize, $centerX, $centerY, $text, $textColor);
                
                // Save image
                imagepng($image, $defaultAvatarPath);
                imagedestroy($image);
            }
        } catch (Exception $e) {
            error_log("Failed to create default avatar: " . $e->getMessage());
        }
    } else {
        error_log("GD library not available for default avatar creation");
    }
}
?>
