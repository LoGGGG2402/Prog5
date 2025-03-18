<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$message = '';
$error = '';

// Get user ID from query string
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : $_SESSION['user_id'];

// Get user details
$user = getUserById($user_id);
if (!$user) {
    redirect('index.php');
}

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Check if it's the logged-in user or a teacher
    if ($user_id == $_SESSION['user_id'] || isTeacher()) {
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);

        // Student can only update their own profile
        if (isStudent() && $user_id != $_SESSION['user_id']) {
            $error = "You don't have permission to update this profile";
        } else {
            // Update profile information
            $updateFields = [];
            $params = [];
            $types = "";

            // For teacher updating student information
            if (isTeacher() && $user['role'] == 'student') {
                $username = sanitize($_POST['username']);
                $fullname = sanitize($_POST['fullname']);

                $updateFields[] = "username = ?";
                $updateFields[] = "fullname = ?";
                $params[] = $username;
                $params[] = $fullname;
                $types .= "ss";

                // Update password if provided
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $updateFields[] = "password = ?";
                    $params[] = $password;
                    $types .= "s";
                }
            }

            $updateFields[] = "email = ?";
            $updateFields[] = "phone = ?";
            $params[] = $email;
            $params[] = $phone;
            $types .= "ss";

            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                // Make sure AVATAR_DIR exists and is writable
                if (!file_exists(AVATAR_DIR)) {
                    mkdir(AVATAR_DIR, 0777, true);
                }

                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $uploadResult = uploadFile($_FILES['avatar'], AVATAR_DIR, $allowedTypes);

                if (isset($uploadResult['success'])) {
                    // Convert file path to URL format - make sure we're using the right format
                    $avatarPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $uploadResult['path']);
                    // Add site URL if it's not an absolute path
                    if (substr($avatarPath, 0, 1) === '/') {
                        $avatarPath = SITE_URL . $avatarPath;
                    }

                    $updateFields[] = "avatar = ?";
                    $params[] = $avatarPath;
                    $types .= "s";
                } else {
                    $error = $uploadResult['error'];
                }
            } elseif (isset($_POST['avatar_url']) && !empty($_POST['avatar_url'])) {
                $avatarUrl = sanitize($_POST['avatar_url']);
                $updateFields[] = "avatar = ?";
                $params[] = $avatarUrl;
                $types .= "s";
            }

            // Proceed with update if no errors
            if (empty($error)) {
                $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $params[] = $user_id;
                $types .= "i";

                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, $types, ...$params);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Profile updated successfully!";

                    // Refresh user data
                    $user = getUserById($user_id);
                } else {
                    $error = "Error updating profile: " . mysqli_error($conn);
                }
            }
        }
    } else {
        $error = "You don't have permission to update this profile";
    }
}

// Handle message operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'send_message':
            $messageText = sanitize($_POST['message']);
            $senderId = $_SESSION['user_id'];
            $receiverId = $user_id;

            // Check if user is trying to message themselves
            if ($senderId == $receiverId) {
                $error = "You cannot send messages to yourself.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iis", $senderId, $receiverId, $messageText);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Message sent successfully!";
                } else {
                    $error = "Failed to send message: " . mysqli_error($conn);
                }
            }
            break;

        case 'edit_message':
            $messageId = (int) $_POST['message_id'];
            $messageText = sanitize($_POST['message']);
            $senderId = $_SESSION['user_id'];

            $stmt = mysqli_prepare($conn, "UPDATE messages SET message = ? WHERE id = ? AND sender_id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $messageText, $messageId, $senderId);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Message updated successfully!";
            } else {
                $error = "Failed to update message: " . mysqli_error($conn);
            }
            break;

        case 'delete_message':
            $messageId = (int) $_POST['message_id'];
            $senderId = $_SESSION['user_id'];

            $stmt = mysqli_prepare($conn, "DELETE FROM messages WHERE id = ? AND sender_id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $messageId, $senderId);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Message deleted successfully!";
            } else {
                $error = "Failed to delete message: " . mysqli_error($conn);
            }
            break;
    }
}

// Mark messages as read if we're viewing someone else's profile
if ($user_id != $_SESSION['user_id']) {
    // Mark all messages from this user as read
    markMessagesAsRead($_SESSION['user_id'], $user_id);
}

// Check if we're coming from a reply action
$from_reply = isset($_GET['from_reply']) && $_GET['from_reply'] == 1;
$sender_id = isset($_GET['sender_id']) ? (int)$_GET['sender_id'] : 0;

// If we arrived at this page from clicking "Reply", mark that sender's messages as read
if ($from_reply && $sender_id > 0) {
    markMessagesAsRead($_SESSION['user_id'], $sender_id);
}

// Fetch messages for this user
$messages = [];
if ($user_id == $_SESSION['user_id']) {
    // If viewing own profile, get recent unread messages from all users
    $messages = getRecentMessages($_SESSION['user_id']);
} else {
    // If viewing another profile, get conversation with that user
    $stmt = mysqli_prepare($conn, "
        SELECT m.*, u.fullname, u.avatar 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $_SESSION['user_id'], $user_id, $user_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
}

$pageTitle = 'Profile: ' . $user['fullname'];
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo $user['fullname']; ?></h4>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?php echo $user['avatar']; ?>" alt="Avatar" class="avatar-sm">
                        <?php else: ?>
                            <img src="img/default-avatar.png" alt="Default Avatar" class="avatar-sm">
                        <?php endif; ?>

                        <ul class="list-group text-left">
                            <li class="list-group-item"><strong>Username:</strong> <?php echo $user['username']; ?></li>
                            <li class="list-group-item"><strong>Role:</strong> <?php echo ucfirst($user['role']); ?>
                            </li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo $user['email']; ?></li>
                            <li class="list-group-item"><strong>Phone:</strong> <?php echo $user['phone']; ?></li>
                        </ul>

                        <?php if ($user_id == $_SESSION['user_id'] || (isTeacher() && $user['role'] == 'student')): ?>
                            <button class="btn btn-primary mt-3" data-toggle="modal" data-target="#editProfileModal">
                                Edit Profile
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>
                            <?php if ($user_id == $_SESSION['user_id']): ?>
                                Recent Unread Messages
                            <?php else: ?>
                                Conversation with <?php echo $user['fullname']; ?>
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body message-container">
                        <?php if (count($messages) > 0): ?>
                            <?php if ($user_id == $_SESSION['user_id']): ?>
                                <!-- Display recent messages from all users when viewing own profile -->
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message message-receiver">
                                        <div class="d-flex align-items-center mb-2 justify-content-between">
                                            <div>
                                                <?php if (!empty($msg['avatar'])): ?>
                                                    <img src="<?php echo $msg['avatar']; ?>" alt="Avatar" class="avatar-sm mr-2">
                                                <?php else: ?>
                                                    <img src="img/default-avatar.png" alt="Default Avatar" class="avatar-sm mr-2">
                                                <?php endif; ?>
                                                <strong><?php echo $msg['fullname']; ?></strong>
                                                <span class="badge badge-<?php echo $msg['role'] === 'teacher' ? 'danger' : 'info'; ?>"><?php echo ucfirst($msg['role']); ?></span>
                                            </div>
                                            <a href="profile.php?id=<?php echo $msg['sender_id']; ?>&from_reply=1&sender_id=<?php echo $msg['sender_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-reply"></i> Reply
                                            </a>
                                        </div>

                                        <p><?php echo $msg['message']; ?></p>

                                        <div class="message-meta d-flex justify-content-between">
                                            <span><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Display conversation with specific user -->
                                <?php foreach ($messages as $msg): ?>
                                    <div
                                        class="message <?php echo ($msg['sender_id'] == $_SESSION['user_id']) ? 'message-sender' : 'message-receiver'; ?>">
                                        <div class="d-flex align-items-center mb-2">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="<?php echo $user['avatar']; ?>" alt="Avatar" class="avatar-sm">
                                            <?php else: ?>
                                                <img src="img/default-avatar.png" alt="Default Avatar" class="avatar-sm">
                                            <?php endif; ?>
                                            <strong><?php echo $msg['fullname']; ?></strong>
                                        </div>

                                        <p><?php echo $msg['message']; ?></p>

                                        <div class="message-meta d-flex justify-content-between">
                                            <span><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></span>

                                            <?php if ($msg['sender_id'] == $_SESSION['user_id']): ?>
                                                <div>
                                                    <button class="btn btn-sm btn-link text-primary edit-message"
                                                        data-id="<?php echo $msg['id']; ?>"
                                                        data-message="<?php echo htmlspecialchars($msg['message']); ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-danger delete-message"
                                                        data-id="<?php echo $msg['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($user_id == $_SESSION['user_id']): ?>
                                <p class="text-center">No new unread messages.</p>
                            <?php else: ?>
                                <p class="text-center">No messages yet. Start a conversation!</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Only show message form if not viewing own profile -->
                    <?php if ($user_id != $_SESSION['user_id']): ?>
                        <div class="card-footer">
                            <form action="" method="post">
                                <input type="hidden" name="action" value="send_message">
                                <div class="form-group">
                                    <textarea class="form-control" name="message" rows="3"
                                        placeholder="Type your message here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="card-footer">
                            <p class="text-muted text-center mb-0">Displaying your most recent messages. Visit a user's
                                profile to send a message.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">

                        <?php if (isTeacher() && $user['role'] == 'student'): ?>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    value="<?php echo $user['username']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="fullname">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    value="<?php echo $user['fullname']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="password">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="<?php echo $user['phone']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="avatar">Upload Avatar</label>
                            <input type="file" class="form-control-file" id="avatar" name="avatar">
                        </div>
                        <div class="form-group">
                            <label for="avatar_url">Or Avatar URL</label>
                            <input type="text" class="form-control" id="avatar_url" name="avatar_url"
                                placeholder="https://...">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Message Modal -->
    <div class="modal fade" id="editMessageModal" tabindex="-1" role="dialog" aria-labelledby="editMessageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMessageModalLabel">Edit Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="" method="post" id="editMessageForm">
                        <input type="hidden" name="action" value="edit_message">
                        <input type="hidden" name="message_id" id="edit_message_id">
                        <div class="form-group">
                            <label for="edit_message_text">Message</label>
                            <textarea class="form-control" id="edit_message_text" name="message" rows="3"
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Message Modal -->
    <div class="modal fade" id="deleteMessageModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMessageModalLabel">Delete Message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this message? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form action="" method="post">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="message_id" id="delete_message_id">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.edit-message').click(function () {
                const id = $(this).data('id');
                const message = $(this).data('message');
                $('#edit_message_id').val(id);
                $('#edit_message_text').val(message);
                $('#editMessageModal').modal('show');
            });

            $('.delete-message').click(function () {
                const id = $(this).data('id');
                $('#delete_message_id').val(id);
                $('#deleteMessageModal').modal('show');
            });
        });
    </script>
</body>

</html>