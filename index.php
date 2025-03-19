<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get all users
$users = $userModel->all('fullname', 'ASC');

// Page title
$pageTitle = 'Classroom Management System';
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>User Directory</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($user['avatar'])): ?>
                                            <img src="<?php echo $user['avatar']; ?>" alt="Avatar" class="avatar-sm">
                                        <?php else: ?>
                                            <img src="img/default-avatar.png" alt="Default Avatar" class="avatar-sm">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['fullname']; ?></td>
                                    <td><?php echo ucfirst($user['role']); ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">View Profile</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isTeacher()): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Teacher Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="manage-students.php" class="list-group-item list-group-item-action">Manage Students</a>
                            <a href="assignments.php" class="list-group-item list-group-item-action">Manage Assignments</a>
                            <a href="submissions.php" class="list-group-item list-group-item-action">View Submissions</a>
                            <a href="challenges.php" class="list-group-item list-group-item-action">Create Challenges</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isStudent()): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Student Dashboard</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="assignments.php" class="list-group-item list-group-item-action">View Assignments</a>
                            <a href="my-submissions.php" class="list-group-item list-group-item-action">My Submissions</a>
                            <a href="challenges.php" class="list-group-item list-group-item-action">Solve Challenges</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
