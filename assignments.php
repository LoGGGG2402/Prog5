<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Page title
$pageTitle = 'Assignments';

// Fetch assignments
$sql = "SELECT assignments.*, users.fullname AS teacher_name FROM assignments JOIN users ON assignments.teacher_id = users.id ORDER BY assignments.created_at DESC";
$result = mysqli_query($conn, $sql);
$assignments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// For each assignment, check if the student has already submitted
if (isStudent()) {
    $student_id = $_SESSION['user_id'];
    foreach ($assignments as &$assignment) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $assignment['id'], $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $assignment['has_submitted'] = mysqli_stmt_num_rows($stmt) > 0;
    }
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
        <div class="row mb-3">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h2>Assignments</h2>
                <?php if (isTeacher()): ?>
                <a href="create-assignment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Assignment
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (count($assignments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Teacher</th>
                                            <th>File</th>
                                            <th>Created At</th>
                                            <?php if (isStudent()): ?>
                                            <th>Action</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?php echo $assignment['title']; ?></td>
                                            <td><?php echo $assignment['description']; ?></td>
                                            <td><?php echo $assignment['teacher_name']; ?></td>
                                            <td>
                                                <a href="serve-file.php?type=assignment&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-info" download>
                                                    <i class="fas fa-download"></i> <?php echo $assignment['filename']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($assignment['created_at'])); ?></td>
                                            <?php if (isStudent()): ?>
                                            <td>
                                                <a href="submit-assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-sm <?php echo $assignment['has_submitted'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <?php echo $assignment['has_submitted'] ? '<i class="fas fa-edit"></i> Update Submission' : '<i class="fas fa-upload"></i> Submit'; ?>
                                                </a>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No assignments available yet.</p>
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
