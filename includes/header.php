<header class="bg-primary py-2">
    <div class="container">
        <div class="header-container">
            <!-- Logo and brand name -->
            <a class="header-brand" href="index.php" title="Home">
                <i class="fas fa-graduation-cap"></i>
                <span class="d-none d-lg-inline">Classroom</span>
                <span class="d-lg-none">CMS</span>
            </a>
            
            <!-- Main navigation -->
            <nav class="header-nav d-none d-md-flex">
                <a class="nav-item py-1 px-2" href="assignments.php" title="Assignments"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
                <a class="nav-item py-1 px-2" href="challenges.php" title="Challenges"><i class="fas fa-puzzle-piece"></i> <span>Challenges</span></a>
                <?php if (isTeacher()): ?>
                <a class="nav-item py-1 px-2" href="manage-students.php" title="Manage"><i class="fas fa-users"></i> <span>Manage</span></a>
                <a class="nav-item py-1 px-2" href="create-user.php" title="Add User"><i class="fas fa-user-plus"></i> <span>Add User</span></a>
                <a class="nav-item py-1 px-2" href="submissions.php" title="Submissions"><i class="fas fa-clipboard-check"></i> <span>Submissions</span></a>
                <?php endif; ?>
                <?php if (isStudent()): ?>
                <a class="nav-item py-1 px-2" href="my-submissions.php" title="My Submissions"><i class="fas fa-file-upload"></i> <span>Submissions</span></a>
                <?php endif; ?>
            </nav>
            
            <!-- Mobile menu button -->
            <button class="header-menu-toggle d-md-none" type="button" data-toggle="collapse" data-target="#mobileMenu" title="Menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- User profile and logout -->
            <?php if (isLoggedIn()): ?>
            <div class="header-user">
                <a class="header-profile py-1 px-2" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" title="Profile">
                    <?php if (!empty($_SESSION['avatar'])): ?>
                        <img src="<?php echo $_SESSION['avatar']; ?>" class="avatar-sm" alt="Profile">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <span><?php echo $_SESSION['fullname']; ?></span>
                    <span class="badge badge-light"><?php echo ucfirst($_SESSION['role']); ?></span>
                </a>
                <a class="header-logout" href="logout.php" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Mobile navigation menu - simplified version -->
        <div class="collapse navbar-collapse d-md-none mt-1" id="mobileMenu">
            <div class="mobile-nav">
                <div class="d-flex flex-wrap">
                    <a class="mobile-nav-item py-1 px-2" href="index.php"><i class="fas fa-home"></i> Home</a>
                    <a class="mobile-nav-item py-1 px-2" href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a>
                    <a class="mobile-nav-item py-1 px-2" href="challenges.php"><i class="fas fa-puzzle-piece"></i> Challenges</a>
                    <?php if (isTeacher()): ?>
                    <a class="mobile-nav-item py-1 px-2" href="manage-students.php"><i class="fas fa-users"></i> Manage</a>
                    <a class="mobile-nav-item py-1 px-2" href="create-user.php"><i class="fas fa-user-plus"></i> Add User</a>
                    <a class="mobile-nav-item py-1 px-2" href="submissions.php"><i class="fas fa-clipboard-check"></i> Submissions</a>
                    <?php endif; ?>
                    <?php if (isStudent()): ?>
                    <a class="mobile-nav-item py-1 px-2" href="my-submissions.php"><i class="fas fa-file-upload"></i> My Submissions</a>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                    <a class="mobile-nav-item py-1 px-2" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                    <a class="mobile-nav-item py-1 px-2" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
