<?php
require_once '../includes/init.php';


// Create connection without selecting a database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating users table: " . mysqli_error($conn));
}

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id CHAR(36) PRIMARY KEY,
    sender_id CHAR(36) NOT NULL,
    receiver_id CHAR(36) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating messages table: " . mysqli_error($conn));
}

// Create assignments table
$sql = "CREATE TABLE IF NOT EXISTS assignments (
    id CHAR(36) PRIMARY KEY,
    teacher_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating assignments table: " . mysqli_error($conn));
}

// Create submissions table
$sql = "CREATE TABLE IF NOT EXISTS submissions (
    id CHAR(36) PRIMARY KEY,
    assignment_id CHAR(36) NOT NULL,
    student_id CHAR(36) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating submissions table: " . mysqli_error($conn));
}

// Create challenges table
$sql = "CREATE TABLE IF NOT EXISTS challenges (
    id CHAR(36) PRIMARY KEY,
    teacher_id CHAR(36) NOT NULL,
    hint TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    result VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
)";
if (!mysqli_query($conn, $sql)) {
    die("Error creating challenges table: " . mysqli_error($conn));
}

// Insert default users if they don't exist
$password = password_hash('123456a@A', PASSWORD_DEFAULT);

$teachers = [
    ['teacher1', 'Teacher One', 'teacher1@example.com', '1234567890'],
    ['teacher2', 'Teacher Two', 'teacher2@example.com', '0987654321']
];

$students = [
    ['student1', 'Student One', 'student1@example.com', '1122334455'],
    ['student2', 'Student Two', 'student2@example.com', '5544332211']
];

// Insert teachers
foreach ($teachers as $teacher) {
    // First check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $teacher[0]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) == 0) {
        // User doesn't exist, create with UUID
        $uuid = generate_uuid();
        $stmt = mysqli_prepare($conn, "INSERT INTO users (id, username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'teacher')");
        mysqli_stmt_bind_param($stmt, "ssssss", $uuid, $teacher[0], $password, $teacher[1], $teacher[2], $teacher[3]);
        mysqli_stmt_execute($stmt);
    }
}

// Insert students
foreach ($students as $student) {
    // First check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $student[0]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) == 0) {
        // User doesn't exist, create with UUID
        $uuid = generate_uuid();
        $stmt = mysqli_prepare($conn, "INSERT INTO users (id, username, password, fullname, email, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
        mysqli_stmt_bind_param($stmt, "ssssss", $uuid, $student[0], $password, $student[1], $student[2], $student[3]);
        mysqli_stmt_execute($stmt);
    }
}

echo "Database and tables created successfully with UUID primary keys!";
?>
