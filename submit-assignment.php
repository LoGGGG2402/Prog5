<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

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
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get assignment details
$stmt = mysqli_prepare($conn, "SELECT * FROM assignments WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$assignment = mysqli_fetch_assoc($result);

// Check if assignment exists
if (!$assignment) {
    redirect('assignments.php');
}

// Check if user has already submitted this assignment
$stmt = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$alreadySubmitted = mysqli_stmt_num_rows($stmt) > 0;

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate file upload
    if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== 0) {
        $error = "Submission file is required";
    } else {
        // Upload file
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'zip'];
        $uploadResult = uploadFile($_FILES['submission_file'], SUBMISSION_DIR, $allowedTypes);
        
        if (isset($uploadResult['success'])) {
            // Insert submission into database
            $studentId = $_SESSION['user_id'];
            $filePath = $uploadResult['path'];
            $fileName = $uploadResult['filename'];
            
            if ($alreadySubmitted) {
                // Update existing submission
                $stmt = mysqli_prepare($conn, "UPDATE submissions SET file_path = ?, filename = ?, created_at = NOW() WHERE assignment_id = ? AND student_id = ?");
                mysqli_stmt_bind_param($stmt, "ssii", $filePath, $fileName, $assignment_id, $studentId);
            } else {
                // Create new submission
                $stmt = mysqli_prepare($conn, "INSERT INTO submissions (assignment_id, student_id, file_path, filename) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiss", $assignment_id, $studentId, $filePath, $fileName);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Assignment submitted successfully!";
                $alreadySubmitted = true;
            } else {
                $error = "Error submitting assignment: " . mysqli_error($conn);
            }
        } else {
            $error = $uploadResult['error'];
        }
    }
}

// Get user's current submission if it exists
$submission = null;
if ($alreadySubmitted) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $submission = mysqli_fetch_assoc($result);
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
                                <strong>Uploaded on:</strong> <?php echo date('M j, Y g:i A', strtotime($assignment['created_at'])); ?>
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
                                    <strong>Submitted on:</strong> <?php echo date('M j, Y g:i A', strtotime($submission['created_at'])); ?>
                                </p>
                                <hr>
                                <p>You can submit again to replace your current submission.</p>
                            </div>
                        <?php endif; ?>
                        
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="submission_file">Your Solution</label>
                                <input type="file" class="form-control-file" id="submission_file" name="submission_file" required>
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
</body>
</html>
