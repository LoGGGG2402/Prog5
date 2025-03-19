<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db.php';

// Include configuration
require_once 'config.php';

// Include functions
require_once 'functions.php';

// Load models
$modelsPath = __DIR__ . '/../models/';

// Autoload model classes
spl_autoload_register(function($className) use ($modelsPath) {
    $file = $modelsPath . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize model instances
$userModel = new User();
$assignmentModel = new Assignment();
$submissionModel = new Submission();
$challengeModel = new Challenge();
$messageModel = new Message();
?>
