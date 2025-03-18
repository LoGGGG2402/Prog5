<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/config.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $hint = sanitize($_POST['hint']);
    $result = sanitize($_POST['result']);
    $teacherId = $_SESSION['user_id'];
    
    // Validate form data
    if (empty($hint)) {
        $error = "Challenge hint is required";
    } elseif (empty($result)) {
        $error = "Challenge result is required";
    } elseif (!isset($_FILES['challenge_file']) || $_FILES['challenge_file']['error'] !== 0) {
        $error = "Challenge file is required";
    } else {
        // Sanitize filename for security
        $originalFileName = pathinfo($_FILES['challenge_file']['name'], PATHINFO_FILENAME);
        
        // Clean filename: standardize spaces, remove special chars
        $newFileName = preg_replace('/\s+/', ' ', $originalFileName);
        $newFileName = preg_replace('/[^a-zA-Z0-9 \-_]/', '', $newFileName);
        $_FILES['challenge_file']['name'] = $newFileName . '.txt';
        
        // Validate directory
        if (!defined('CHALLENGE_DIR') || !is_dir(CHALLENGE_DIR)) {
            $error = "Upload directory is not properly configured";
        } else {
            // Upload file
            $allowedTypes = ['txt'];
            $uploadResult = uploadFile($_FILES['challenge_file'], CHALLENGE_DIR, $allowedTypes);
            
            if (isset($uploadResult['success'])) {
                $filePath = $uploadResult['path'];
                
                // Security check: ensure file is within the intended directory
                if (strpos(realpath($filePath), realpath(CHALLENGE_DIR)) !== 0) {
                    $error = "Security violation detected in file path";
                    @unlink($filePath);
                } else {
                    // Save to database
                    $result = dbExecute(
                        "INSERT INTO challenges (teacher_id, hint, file_path, result) VALUES (?, ?, ?, ?)",
                        "isss", 
                        [$teacherId, $hint, $filePath, $result]
                    );
                    
                    if ($result !== false) {
                        $message = "Challenge created successfully!";
                    } else {
                        $error = "Error creating challenge: Database error";
                    }
                }
            } else {
                $error = $uploadResult['error'];
            }
        }
    }
}

$pageTitle = 'Create Challenge';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <?php if (!empty($message)): ?>
            <?php echo showSuccess($message); ?>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <?php echo showError($error); ?>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-puzzle-piece mr-2"></i>Create Challenge</h4>
                        <a href="challenges.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Challenges
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="hint">Challenge Hint</label>
                                <textarea class="form-control" id="hint" name="hint" rows="4" required></textarea>
                                <small class="form-text text-muted">Provide a hint that will help students solve the challenge.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="result">Challenge Result</label>
                                <input type="text" class="form-control" id="result" name="result" required>
                                <small class="form-text text-muted">Enter the result that students need to guess.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="challenge_file">Upload Challenge File (TXT only)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="challenge_file" name="challenge_file" required>
                                    <label class="custom-file-label" for="challenge_file">Choose file...</label>
                                </div>
                                <small class="form-text text-muted mt-2">The file will contain the challenge content.</small>
                                <div class="alert alert-info mt-3">
                                    <strong>Note:</strong> Students will now guess the result rather than the filename.
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Create Challenge
                                </button>
                                <a href="challenges.php" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
