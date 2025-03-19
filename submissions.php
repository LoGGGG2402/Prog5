<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Only teachers can view all submissions
if (!isTeacher()) {
    redirect('assignments.php');
}

$message = '';
$error = '';

// Get assignment ID from query string if provided
$assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

// Get all assignments for the dropdown filter using Assignment model
$assignments = $assignmentModel->all('created_at', 'DESC');

// Get submissions based on filter
$filter = [];
if ($assignment_id > 0) {
    $filter['assignment_id'] = $assignment_id;
}
$submissions = $submissionModel->getSubmissionsWithDetails($filter);

$pageTitle = 'View Submissions';
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Student Submissions</h4>
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="assignmentFilterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo $assignment_id > 0 ? 'Filter: Selected Assignment' : 'All Assignments'; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="assignmentFilterDropdown">
                                <a class="dropdown-item" href="submissions.php">All Assignments</a>
                                <div class="dropdown-divider"></div>
                                <?php foreach ($assignments as $assignment): ?>
                                    <a class="dropdown-item <?php echo $assignment_id == $assignment['id'] ? 'active' : ''; ?>" 
                                       href="submissions.php?assignment_id=<?php echo $assignment['id']; ?>">
                                        <?php echo $assignment['title']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($submissions) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Assignment</th>
                                            <th>File</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($submission['avatar'])): ?>
                                                        <img src="<?php echo $submission['avatar']; ?>" class="avatar-sm mr-2" alt="Profile">
                                                    <?php else: ?>
                                                        <i class="fas fa-user-circle mr-2"></i>
                                                    <?php endif; ?>
                                                    <?php echo $submission['student_name']; ?> 
                                                    <small>(<?php echo $submission['username']; ?>)</small>
                                                </td>
                                                <td><?php echo $submission['assignment_title']; ?></td>
                                                <td><?php echo $submission['filename']; ?></td>
                                                <td><?php echo formatDate($submission['created_at']); ?></td>
                                                <td>
                                                    <a href="serve-file.php?type=submission&id=<?php echo $submission['id']; ?>" class="btn btn-sm btn-primary" download>
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    <a href="profile.php?id=<?php echo $submission['student_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-user"></i> Student Profile
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center my-4">No submissions found.</p>
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
