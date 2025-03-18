<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

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

// Get all assignments for the dropdown filter - use a function to simplify
$assignments = getAllAssignments();

// Get submissions based on filter
$submissions = getSubmissionsByAssignment($assignment_id);

$pageTitle = 'View Submissions';

/**
 * Get all assignments for dropdown
 * @return array
 */
function getAllAssignments() {
    global $conn;
    $assignments = [];
    $stmt = mysqli_prepare($conn, "SELECT id, title FROM assignments ORDER BY created_at DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $assignments;
}

/**
 * Get submissions filtered by assignment if needed
 * @param int $assignment_id
 * @return array
 */
function getSubmissionsByAssignment($assignment_id = 0) {
    global $conn;
    $submissions = [];
    
    $sql = "SELECT s.*, a.title as assignment_title, u.fullname as student_name, u.username, u.avatar
            FROM submissions s 
            JOIN assignments a ON s.assignment_id = a.id 
            JOIN users u ON s.student_id = u.id ";

    if ($assignment_id > 0) {
        $sql .= "WHERE s.assignment_id = ? ";
        $params = [$assignment_id];
        $types = "i";
    } else {
        $params = [];
        $types = "";
    }
    
    $sql .= "ORDER BY s.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['download_url'] = "serve-file.php?type=submission&id=" . $row['id'];
        $submissions[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $submissions;
}
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
                                                <td><?php echo date('M j, Y g:i A', strtotime($submission['created_at'])); ?></td>
                                                <td>
                                                    <a href="<?php echo $submission['download_url']; ?>" class="btn btn-sm btn-primary" download>
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
