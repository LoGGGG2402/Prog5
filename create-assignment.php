<?php
require_once 'includes/init.php';
require_once 'utils/FileHandler.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $teacherId = $_SESSION['user_id'];
    
    // Check if title is provided
    if (empty($title)) {
        $error = "Assignment title is required";
    } 
    // Check if file is uploaded
    elseif (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== 0) {
        $error = "Assignment file is required";
    } else {
        // Upload file using FileHandler utility
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'zip'];
        $uploadResult = FileHandler::uploadFile($_FILES['assignment_file'], ASSIGNMENT_DIR, $allowedTypes);
        
        if (isset($uploadResult['success'])) {
            // Prepare file path for DB insertion
            $filePath = $uploadResult['path'];
            $fileName = $uploadResult['filename'];
            
            // Create assignment using the Assignment model
            $assignmentData = [
                'teacher_id' => $teacherId,
                'title' => $title,
                'description' => $description,
                'file_path' => $filePath,
                'filename' => $fileName
            ];
            
            $newId = $assignmentModel->create($assignmentData);
            
            if ($newId) {
                $message = "Assignment created successfully!";
            } else {
                $error = "Error creating assignment";
            }
        } else {
            $error = $uploadResult['error'];
        }
    }
}

$pageTitle = 'Create Assignment';
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
                        <h4><i class="fas fa-tasks mr-2"></i>Create Assignment</h4>
                        <a href="assignments.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Assignments
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="title">Assignment Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="assignment_file">Upload Assignment File</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="assignment_file" name="assignment_file" required>
                                    <label class="custom-file-label" for="assignment_file">Choose file...</label>
                                </div>
                                <small class="form-text text-muted mt-2">Allowed file types: PDF, DOC, DOCX, TXT, ZIP</small>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Create Assignment
                                </button>
                                <a href="assignments.php" class="btn btn-secondary">
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
