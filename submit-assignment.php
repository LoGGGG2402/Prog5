<?php
require_once 'includes/init.php';
require_once 'utils/FileHandler.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Only students can submit assignments
if (!isStudent()) {
    redirect('assignments.php');
}

$message = '';
$error = '';

// Get assignment ID from query string
$assignment_id = isset($_GET['id']) ? $_GET['id'] : '';

// Get assignment details using Assignment model
$assignment = $assignmentModel->find($assignment_id);

// Check if assignment exists
if (!$assignment) {
    redirect('assignments.php');
}

// Check if user has already submitted this assignment
$submission = $submissionModel->findByAssignmentAndStudent($assignment_id, $_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload submission file
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'zip'];
    $uploadResult = FileHandler::uploadFile($_FILES['submission_file'], SUBMISSION_DIR, $allowedTypes);
    
    if (isset($uploadResult['success'])) {
        // Prepare submission data
        $submissionData = [
            'assignment_id' => $assignment_id,
            'student_id' => $_SESSION['user_id'],
            'file_path' => $uploadResult['path'],
            'filename' => $uploadResult['filename']
        ];
        
        // Save submission using the Submission model
        $result = $submissionModel->saveSubmission($submissionData);
        
        if ($result['success']) {
            $message = $result['message'];
            
            // Refresh submission data
            $submission = $submissionModel->find($result['id']);
        } else {
            $error = $result['error'];
        }
    } else {
        $error = $uploadResult['error'];
    }
}

$pageTitle = 'Submit Assignment';
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
                    <div class="card-header">
                        <h4>Submit Assignment: <?php echo $assignment['title']; ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="assignment-details mb-4">
                            <h5>Assignment Details</h5>
                            <p><?php echo $assignment['description']; ?></p>
                            <p>
                                <strong>File:</strong> <?php echo $assignment['filename']; ?>
                                <a href="serve-file.php?type=assignment&id=<?php echo $assignment_id; ?>" class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </p>
                            <p>
                                <strong>Uploaded on:</strong> <?php echo formatDate($assignment['created_at']); ?>
                            </p>
                        </div>
                        
                        <?php if ($submission): ?>
                            <div class="alert alert-info">
                                <h5>Your Current Submission</h5>
                                <p>
                                    <strong>File:</strong> <?php echo $submission['filename']; ?>
                                    <a href="serve-file.php?type=submission&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </p>
                                <p>
                                    <strong>Submitted on:</strong> <?php echo formatDate($submission['created_at']); ?>
                                </p>
                                <hr>
                                <p>You can submit again to replace your current submission.</p>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="submission_file">Your Solution</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="submission_file" name="submission_file" required>
                                    <label class="custom-file-label" for="submission_file">Choose file...</label>
                                </div>
                                <small class="form-text text-muted">Allowed file types: PDF, DOC, DOCX, TXT, ZIP</small>
                            </div>
                            <button type="submit" class="btn btn-success">Submit Assignment</button>
                            <a href="assignments.php" class="btn btn-secondary">Back to Assignments</a>
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
    <script>
        // Show file name when file is selected
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    </script>
</body>
</html>
