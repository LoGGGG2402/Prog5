<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Page title
$pageTitle = 'My Submissions';

// Fetch submissions
$userId = $_SESSION['user_id'];
$sql = "SELECT submissions.*, assignments.title AS assignment_title FROM submissions JOIN assignments ON submissions.assignment_id = assignments.id WHERE submissions.student_id = $userId ORDER BY submissions.created_at DESC";
$result = mysqli_query($conn, $sql);
$submissions = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
        <div class="row mb-3">
            <div class="col-md-12">
                <h2>My Submissions</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-file-upload mr-2"></i>Assignment Submissions</h4>
                        <a href="assignments.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-tasks mr-1"></i> View Assignments
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($submissions) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Assignment</th>
                                            <th>File</th>
                                            <th>Submitted At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo $submission['assignment_title']; ?></td>
                                            <td><?php echo $submission['filename']; ?></td>
                                            <td><?php echo date('d M Y, h:i A', strtotime($submission['created_at'])); ?></td>
                                            <td>
                                                <a href="serve-file.php?type=submission&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-info" download>
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                                <a href="submit-assignment.php?id=<?php echo $submission['assignment_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Update
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                You haven't submitted any assignments yet. 
                                <a href="assignments.php" class="alert-link">View available assignments</a> to get started.
                            </div>
                        <?php endif; ?>
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
