# Classroom Management System

A comprehensive web-based platform designed to facilitate interactions between teachers and students in an educational environment.

## Overview

The Classroom Management System is a PHP-based web application that provides an interactive platform for teachers and students. It enables teachers to create assignments, manage challenges, monitor student submissions, and communicate with students. Students can access assignments, submit their work, solve challenges, and communicate with teachers.

This system follows modern PHP development practices including MVC architecture principles and OOP methodologies, with a focus on security, usability, and maintainability.

## Features

### User Management
- **User Authentication**: Secure login system for teachers and students with password hashing
  - BCrypt password hashing with PHP's native password_hash() function
  - Session-based authentication with secure session handling
  - Protection against brute force attacks
  - Automatic session termination on logout
- **Profile Management**: Users can update their personal information, contact details, and avatars
  - Email and phone number updates
  - Avatar upload with image validation
  - Support for remote avatar URLs
- **Role-Based Access Control**: Different features for teachers and students with permission checks
  - Role verification on all protected pages
  - Different navigation options based on user role
  - Access control functions that prevent unauthorized access

### Assignments
- **Create Assignments**: Teachers can create assignments with titles, descriptions, and file attachments
  - File upload with comprehensive validation
  - Multiple file format support (PDF, DOC, DOCX, TXT, ZIP)
- **Assignment Listing**: Students can view available assignments with teacher information
  - Teacher identification for each assignment
  - Creation date display
  - Download functionality
- **Submission System**: Students can submit their work for assignments with file uploads
  - File type validation
  - Confirmation messages after submission
- **Resubmission Support**: Students can update their submissions if needed
  - Previous submission display
  - Submission history tracking

### Messaging
- **Direct Communication**: Users can send messages to each other
  - Teacher-student communication
  - Student-student communication
- **Message Management**: View and track message history
  - Read status tracking
  - Chronological organization
- **Profile Integration**: Access messaging from user profiles
  - Quick access to messaging interface
  - Contact information display

### File Management
- **Secure File Uploads**: Support for various file types with validation
  - Server-side file validation
  - File type restriction based on context
  - Automatic file rejection for invalid types
- **File Downloads**: Access to assignment and submission files
  - Direct download links
  - Proper HTTP headers for downloads
  - Secure file serving
- **Directory Protection**: Prevention of directory traversal attacks
  - Path sanitization for all file operations
  - Validation of file paths against directory roots

## Technical Details

### System Requirements
- PHP 7.0+ (recommended PHP 7.4+)
- MySQL 5.7+ (or MariaDB 10.2+)
- Web server (Apache/Nginx)
- PHP Extensions:
  - mysqli
  - fileinfo
  - session
  - json
  - mbstring

### File Structure
- `/includes`: Core system files and configuration
  - `config.php`: System configuration (DB, paths, URLs)
  - `db.php`: Database connection handling
  - `functions.php`: Helper functions
  - `init.php`: System initialization
  - `header.php` & `footer.php`: Common templates
- `/models`: Data models for entities
- `/utils`: Utility classes including FileHandler
- `/css`: Stylesheets
- `/js`: JavaScript files
- `/uploads`: Upload directories (avatars, assignments, submissions)

### Database Structure
- `users`: User account information
- `assignments`: Assignment details and files
- `submissions`: Student assignment submissions
- `messages`: Inter-user communication

## Installation

1. Clone the repository to your web server directory

2. Create a MySQL database named `classroom_management`
   ```sql
   CREATE DATABASE classroom_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'prog5user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON classroom_management.* TO 'prog5user'@'localhost';
   ```

3. Update database credentials in `includes/config.php`

4. Update site URL in `includes/config.php`

5. Run the setup script by visiting `setup/database.php` in your browser

6. Ensure all upload directories are writable by the web server:
   ```bash
   mkdir -p uploads/{avatars,assignments,submissions}
   chmod -R 755 uploads
   chown -R www-data:www-data uploads
   ```

7. Configure PHP settings in php.ini:
   ```
   upload_max_filesize = 20M
   post_max_size = 20M
   memory_limit = 128M
   ```

8. Access the application through your web browser

### Directory Permissions
Make sure these directories are writable by the web server:
  - `/uploads/avatars`: For profile pictures
  - `/uploads/assignments`: For teacher assignments
  - `/uploads/submissions`: For student submissions
  - `/img`: For system images

## Usage

### Teacher Access
- Login using teacher credentials
- Create assignments for students
- View student submissions
- Communicate with students from user profiles

### Student Access
- Login using student credentials
- View and download assignments
- Submit completed assignments
- Update submissions if needed
- Communicate with teachers and other students

## Security Features

- Password hashing using PHP's native password functions
- Input sanitization for all user-provided data
- Prevention of directory traversal attacks
- MIME type validation for uploaded and downloaded files
- Session management with secure cookie settings
- Role-based access control for all operations
- XSS prevention through output escaping

## Troubleshooting

### Common Issues and Solutions

#### Upload Permissions
- **Issue**: Files cannot be uploaded, "Failed to create directory" errors
- **Solution**: Check and correct directory permissions
  ```bash
  chmod -R 755 /path/to/uploads
  chown -R www-data:www-data /path/to/uploads
  ```

#### Database Connection
- **Issue**: "Connection failed" errors
- **Solution**: Verify credentials in config.php

#### File Uploads Fail
- **Issue**: Large files fail to upload
- **Solution**: Check PHP limits in php.ini
  ```
  upload_max_filesize = 20M
  post_max_size = 20M
  ```

## Project Pages

### Common Pages
- `index.php`: User directory and dashboard
- `assignments.php`: Assignment listing
- `profile.php`: User profile viewing and messaging
- `serve-file.php`: Secure file download handler

### Teacher-Only Pages
- `create-assignment.php`: Assignment creation
- `submissions.php`: View all student submissions
- `manage-students.php`: Student management

### Student-Only Pages
- `submit-assignment.php`: Assignment submission
- `my-submissions.php`: View personal submission history
