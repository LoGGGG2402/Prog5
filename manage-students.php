<?php
require_once 'includes/init.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

// Page title
$pageTitle = 'Manage Students';

// Fetch students using User model
$students = $userModel->getAllStudents();
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Manage Students</h4>
                        <a href="create-user.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Add New User
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($student['avatar'])): ?>
                                                <img src="<?php echo $student['avatar']; ?>" alt="Avatar" class="avatar-sm">
                                            <?php else: ?>
                                                <img src="img/default-avatar.png" alt="Default Avatar" class="avatar-sm">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $student['fullname']; ?></td>
                                        <td><?php echo $student['email']; ?></td>
                                        <td><?php echo $student['phone']; ?></td>
                                        <td>
                                            <a href="profile.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-user"></i> View Profile
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (count($students) == 0): ?>
                            <p class="text-center my-3">No students found.</p>
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
