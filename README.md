# Classroom Management System

A comprehensive web-based platform designed to facilitate interactions between teachers and students in an educational environment.

## Overview

The Classroom Management System is a PHP-based web application that provides an interactive platform for teachers and students. It enables teachers to create assignments, manage challenges, monitor student submissions, and communicate with students. Students can access assignments, submit their work, solve challenges, and communicate with teachers.

This system is built with a focus on security, usability, and maintainability, following modern PHP development practices including MVC architecture principles and OOP methodologies. The application demonstrates best practices in web development, database handling, and secure user authentication.

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
  - Real-time profile information updates
- **Role-Based Access Control**: Different features for teachers and students with permission checks
  - Role verification on all protected pages
  - Different navigation options based on user role
  - Access control functions that prevent unauthorized access
  - Role-specific dashboard interfaces
- **Avatar System**: Upload personal photos or use URL-based avatars with proper validation
  - Support for JPG, JPEG, PNG, and GIF formats
  - Automatic resizing and optimization
  - Default avatar generation for new accounts
  - Preview functionality before confirming changes

### Assignments
- **Create Assignments**: Teachers can create assignments with titles, descriptions, and file attachments
  - Rich text description support
  - File upload with comprehensive validation
  - Multiple file format support
  - Assignment preview before publishing
- **Assignment Listing**: Students can view available assignments with teacher information
  - Chronological ordering with newest assignments first
  - Teacher identification for each assignment
  - Clear file format indication
  - Creation date display
- **Submission System**: Students can submit their work for assignments with file uploads
  - Drag-and-drop file upload interface
  - Progress indicators during upload
  - Confirmation messages after submission
  - Automatic file type validation
- **Resubmission Support**: Students can update their submissions if needed
  - Previous submission display
  - Submission history tracking
  - Clear indication of latest submission
  - Timestamp recording for each version
- **File Type Validation**: Secure handling of various document formats (PDF, DOC, DOCX, TXT, ZIP)
  - MIME type verification
  - Extension checking
  - File size limitations
  - Malicious content scanning

### Challenges
- **Challenge Creation**: Teachers can create educational challenges with hints and expected solutions
  - Detailed hint formatting options
  - Solution definition with case-sensitivity options
  - File attachment for challenge content
  - Preview before publishing
- **Challenge Solving**: Students can attempt to solve challenges by submitting answers
  - Multiple attempt support
  - Real-time answer validation
  - Clear success/failure indicators
  - Session-based solved status tracking
- **Answer Verification**: Automatic verification of student answers against expected solutions
  - Case-insensitive matching option
  - Exact match requirements
  - Instant feedback to students
  - Prevention of brute force attempts
- **Content Reveal**: Challenge content is revealed only after correct answers are provided
  - Progressive content disclosure
  - Secure content protection
  - Persistent unlock status for solved challenges
  - In-browser content preview
- **Download Support**: Challenge files can be downloaded once solved
  - Secure file serving mechanism
  - Content disposition headers for proper download
  - Prevention of unauthorized downloads
  - Support for various file formats

### Communication
- **Messaging System**: Direct messaging between teachers and students
  - Real-time conversation interface
  - Thread-based message organization
  - Persistent message storage
  - Support for formatting options
- **Message Management**: Edit and delete functionality for sent messages
  - Message editing with version tracking
  - Message deletion confirmation
  - Time-based editing restrictions
  - Clear indication of edited messages
- **Unread Message Indicators**: Visual notification of unread messages
  - Badge counters for unread messages
  - Color-coded unread status
  - Automatic marking as read when viewed
  - Filter options for read/unread messages
- **Profile Viewing**: Access to user profiles with contact information
  - Comprehensive profile information display
  - Contact details with privacy controls
  - Role identification
  - Message history access
- **Reply System**: Quick reply functionality from profile pages
  - Inline reply forms
  - Quote functionality
  - Keyboard shortcuts for common actions
  - Attachment options for responses

### File Management
- **Secure File Uploads**: Support for various file types with validation
  - Server-side file validation
  - File type restriction based on context
  - Virus/malware scanning capability
  - Automatic file rejection for invalid types
- **File Downloads**: Access to assignment, submission, and challenge files
  - Direct download links
  - Proper HTTP headers for downloads
  - Bandwidth throttling for large files
  - Download tracking and logging
- **MIME-Type Detection**: Proper content-type headers for downloads
  - Dynamic MIME type detection
  - Custom MIME type mapping
  - Fallback to safe default types
  - Browser compatibility considerations
- **Directory Protection**: Prevention of directory traversal attacks
  - Path sanitization for all file operations
  - Restrictions on accessing files outside designated directories
  - Validation of file paths against directory roots
  - Error logging for attempted violations
- **Unique Filenames**: Generation of unique filenames to prevent overwrites
  - Timestamp-based naming
  - UUID generation for filenames
  - Collision detection and prevention
  - Original filename preservation in database

## Technical Details

### System Architecture
The application follows a lightweight MVC-inspired architecture:
- **Models**: Handle data operations and business logic
  - Database abstraction layer with prepared statements
  - Business logic encapsulation
  - Data validation rules
  - Relationship management between entities
- **Views**: PHP templates for rendering HTML
  - Separation of presentation from business logic
  - Reusable template components
  - Consistent styling and layout
  - Responsive design considerations
- **Controllers**: Logic spread across page scripts that coordinate between models and views
  - Request handling and routing
  - Input validation and sanitization
  - Authentication and authorization checks
  - Response generation and formatting

### System Requirements
- PHP 7.0+ (recommended PHP 7.4+)
  - Core PHP features utilized
  - OOP functionality requirements
  - Modern PHP syntax support
  - Error handling capabilities
- MySQL 5.7+ (or MariaDB 10.2+)
  - InnoDB storage engine
  - UTF-8mb4 character set and collation
  - Foreign key constraint support
  - Transaction capability
- Web server (Apache/Nginx)
  - URL rewriting support (optional)
  - .htaccess support for Apache
  - PHP module integration
  - File upload size configuration
- PHP Extensions:
  - mysqli: For database operations
  - fileinfo: For file type detection
  - gd: For image processing (optional, for avatar generation)
  - session: For user session management
  - json: For data encoding/decoding
  - mbstring: For proper UTF-8 string handling

### File Structure
- `/includes`: Core system files and configuration
  - `config.php`: System configuration including database and path settings
    - Database connection parameters
    - File path definitions
    - URL configurations
    - System constants
  - `db.php`: Database connection handling with mysqli
    - Connection establishment
    - Character set configuration
    - Error handling
  - `functions.php`: Helper functions for common operations
    - Authentication utilities
    - Input sanitization
    - Output formatting
    - Navigation helpers
  - `init.php`: System initialization and model loading
    - Session configuration
    - Database initialization
    - Model autoloading
    - Global variables setup
  - `header.php`: Common header template
    - Navigation menu
    - User information display
    - Responsive design elements
  - `footer.php`: Common footer template
    - Copyright information
    - Quick links
    - Contact information
- `/models`: Data models
  - `Model.php`: Base model class with database operations (CRUD)
    - Query builder functionality
    - Transaction support
    - Error handling
    - Type detection for parameters
  - `User.php`: User-related operations including authentication
    - Login verification
    - Password handling
    - Profile management
    - Role checking
  - `Assignment.php`: Assignment management
    - Assignment creation
    - Listing with filtering
    - Association with teachers
    - Student submission status
  - `Submission.php`: Submission handling and status tracking
    - Submission creation/updating
    - File association
    - Status tracking
    - Student-assignment relationships
  - `Challenge.php`: Challenge management and answer verification
    - Challenge creation
    - Answer validation
    - Content protection
    - Progress tracking
  - `Message.php`: Messaging functionality and read status
    - Message sending
    - Conversation threading
    - Read status management
    - Message editing/deletion
- `/utils`: Utility classes
  - `FileHandler.php`: File upload and management utilities with security features
    - Secure upload processing
    - MIME type detection
    - File serving
    - Path sanitization
- `/css`: Stylesheets
  - `style.css`: Main application styles
    - Layout definitions
    - Component styling
    - Responsive breakpoints
    - Animation effects
- `/js`: JavaScript files
  - `common.js`: Common JavaScript functions
    - Form handling
    - UI interactions
    - AJAX functionality
    - Validation routines
- `/setup`: Installation scripts
  - `database.php`: Database initialization script
    - Table creation
    - Default data insertion
    - Index generation
    - Foreign key setup
- `/uploads`: Upload directories for files (created by application)
  - `/avatars`: User profile pictures
    - Format: JPG, PNG, GIF
    - Size: Up to 2MB
    - Dimensions: Auto-resized to standard format
  - `/assignments`: Teacher assignment files
    - Format: PDF, DOC, DOCX, TXT, ZIP
    - Size: Up to 10MB
    - Organization: UUID-based filenames
  - `/submissions`: Student submission files
    - Format: PDF, DOC, DOCX, TXT, ZIP
    - Size: Up to 10MB
    - Organization: Assignment-student relationship
  - `/challenges`: Challenge content files
    - Format: TXT primarily
    - Size: Up to 5MB
    - Protection: Access controlled by answer verification

### Database Structure
- `users`: User account information
  - `id`: Primary key, auto-increment
  - `username`: Unique username, VARCHAR(50)
  - `password`: Hashed password, VARCHAR(255)
  - `fullname`: User's full name, VARCHAR(100)
  - `email`: Email address, VARCHAR(100)
  - `phone`: Contact phone, VARCHAR(20)
  - `role`: User role (teacher/student), ENUM
  - `avatar`: Path to profile image, VARCHAR(255), nullable
  - `created_at`: Account creation timestamp, TIMESTAMP
  - Indexes: Primary key on id, Unique index on username
  - Constraints: Check constraint on role values
- `assignments`: Assignment details and files
  - `id`: Primary key, auto-increment
  - `teacher_id`: Foreign key to teacher's user ID, INT(11)
  - `title`: Assignment title, VARCHAR(255)
  - `description`: Assignment description, TEXT, nullable
  - `file_path`: Path to assignment file, VARCHAR(255)
  - `filename`: Original filename, VARCHAR(255)
  - `created_at`: Creation timestamp, TIMESTAMP
  - Indexes: Primary key on id, Index on teacher_id
  - Constraints: Foreign key to users(id) on teacher_id
- `submissions`: Student assignment submissions
  - `id`: Primary key, auto-increment
  - `assignment_id`: Foreign key to assignment, INT(11)
  - `student_id`: Foreign key to student's user ID, INT(11)
  - `file_path`: Path to submission file, VARCHAR(255)
  - `filename`: Original filename, VARCHAR(255)
  - `created_at`: Submission timestamp, TIMESTAMP
  - Indexes: Primary key on id, Composite index on (assignment_id, student_id)
  - Constraints: Foreign keys to assignments(id) and users(id)
- `challenges`: Teacher-created challenges
  - `id`: Primary key, auto-increment
  - `teacher_id`: Foreign key to teacher's user ID, INT(11)
  - `hint`: Challenge hint/instruction, TEXT
  - `file_path`: Path to challenge file, VARCHAR(255)
  - `result`: Expected answer to challenge, VARCHAR(255)
  - `created_at`: Creation timestamp, TIMESTAMP
  - Indexes: Primary key on id, Index on teacher_id
  - Constraints: Foreign key to users(id) on teacher_id
- `messages`: Inter-user communication
  - `id`: Primary key, auto-increment
  - `sender_id`: Foreign key to sender's user ID, INT(11)
  - `receiver_id`: Foreign key to receiver's user ID, INT(11)
  - `message`: Message content, TEXT
  - `is_read`: Read status flag, BOOLEAN, default 0
  - `created_at`: Message timestamp, TIMESTAMP
  - Indexes: Primary key on id, Indexes on sender_id and receiver_id
  - Constraints: Foreign keys to users(id) for both sender and receiver

## Implementation Details

### Authentication System
- Session-based authentication with secure session handling
  - PHP's native session management
  - Session regeneration on privilege level changes
  - Cookie security parameters (httponly, secure, samesite)
  - Session timeout configuration
- Password hashing using PHP's `password_hash()` and `password_verify()`
  - BCrypt algorithm implementation
  - Customizable work factor
  - Salt generation and management
  - Future-proof upgrade path
- Role verification for protected pages
  - Function-based role checking
  - Role verification on every protected page
  - Granular permission system
  - Redirect to appropriate pages based on role
- Login state persistence across pages
  - Session variable management
  - Remember me functionality (optional)
  - Secure session data storage
  - Proper session cleanup on logout

### Database Operations
- Prepared statements for all database queries to prevent SQL injection
  - Parameter binding for all user input
  - Clear separation of SQL and data
  - Type specification for parameters
  - Error handling for failed queries
- Parameterized queries with type binding
  - Automatic type detection
  - Manual type specification when needed
  - Support for all MySQL data types
  - Proper handling of NULL values
- Abstracted database operations through the Model class
  - Reusable CRUD operations
  - Method chaining support
  - Query builder pattern
  - Transaction management
- Transaction support for complex operations
  - Atomic operations
  - Rollback capability
  - Nested transaction handling
  - Error management and logging

### File Handling
- Secure file uploads with type and size validation
  - Multiple validation layers
  - Size restriction enforcement
  - File extension whitelist
  - MIME type validation
- File type determination based on extension and content
  - Extension checking
  - MIME type detection
  - Content analysis for certain formats
  - Multiple validation points
- Prevention of directory traversal through path sanitization
  - Path component filtering
  - Directory verification
  - Whitelist approach to file locations
  - Realpath validation
- Proper MIME type handling for downloads
  - Content-Type header setting
  - Content-Disposition configuration
  - Download filename sanitization
  - Browser compatibility handling

### Security Measures
- Input sanitization for all user inputs
  - HTML special character encoding
  - SQL injection prevention
  - Cross-site scripting protection
  - Multiple sanitization layers
- Output escaping to prevent XSS attacks
  - Context-aware escaping
  - HTML entity encoding
  - JavaScript string escaping
  - CSS value sanitization
- CSRF protection for form submissions
  - Token generation and validation
  - Per-session token management
  - Token expiration handling
  - Multiple token support for concurrent forms
- Path traversal protection
  - Directory whitelisting
  - Path component validation
  - Realpath usage for canonicalization
  - Error logging for suspicious requests
- Secure file handling with proper permissions
  - Minimal required permissions
  - Separate upload directories
  - Permission verification before operations
  - Automatic permission correction
- Role-based access control for all operations
  - Function-level permission checking
  - UI element visibility control
  - Server-side verification
  - Robust permission denial handling
- Password hashing with modern algorithms
  - BCrypt implementation
  - Work factor adjustment capability
  - Salt management
  - Hash upgrade path for future algorithms
- Data validation and sanitization
  - Type checking and validation
  - Format validation (email, phone, etc.)
  - Range checking for numeric values
  - Strict comparison operations
- Secure session management
  - Session data encryption
  - Session identifier protection
  - Session timeout management
  - Session cleanup on logout
- Protection against common web vulnerabilities
  - SQL injection mitigation
  - XSS protection
  - CSRF prevention
  - Authentication bypass prevention

## Security Features

- Password hashing using PHP's native password functions
  - BCrypt algorithm with cost factor 10
  - Automatic salt generation and management
  - Future-proof design for algorithm upgrades
  - Protection against rainbow table attacks
- Input sanitization for all user-provided data
  - HTML special character encoding
  - MySQL real escape string for database inputs
  - Whitelist validation for critical inputs
  - Multiple sanitization layers
- Prevention of directory traversal attacks through path validation
  - Realpath function to resolve canonical paths
  - Path component validation
  - Restriction to whitelisted directories
  - Error logging for suspicious path requests
- MIME type validation for uploaded and downloaded files
  - Extension checking
  - Content-based type detection
  - Whitelist of allowed types per context
  - Default safe content types
- Session management with proper security controls
  - Session timeout configuration
  - Secure cookie settings (httponly, secure, samesite attributes)
  - Session regeneration on privilege changes
  - Session fixation prevention
- Role-based access control for all operations
  - Permission verification on every protected page
  - UI element visibility control based on role
  - Function-level permission checking
  - Graceful handling of unauthorized attempts
- XSS prevention through output escaping
  - HTML entity encoding for displayed data
  - Context-aware escaping
  - JavaScript string sanitization
  - Parameter encoding in URLs

## Installation

1. Clone the repository to your web server directory
   ```bash
   git clone https://github.com/username/classroom-management.git /var/www/html/classroom
   ```

2. Create a MySQL database named `classroom_management`
   ```sql
   CREATE DATABASE classroom_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'prog5user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON classroom_management.* TO 'prog5user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Update database credentials in `includes/config.php`:
   ```php
   define('DB_HOST', 'your_host');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'classroom_management');
   ```

4. Update site URL in `includes/config.php`:
   ```php
   define('SITE_URL', 'http://your-site-url');
   ```

5. Run the setup script by visiting `setup/database.php` in your browser
   - This will create all necessary tables
   - Default user accounts will be created
   - Initial directory structure will be established
   - Sample data can be populated (optional)

6. Ensure all directories in `config.php` are writable by the web server:
   ```bash
   mkdir -p /var/www/html/classroom/uploads/{avatars,assignments,submissions,challenges}
   chmod -R 755 /var/www/html/classroom/uploads
   chown -R www-data:www-data /var/www/html/classroom/uploads
   ```

7. Configure PHP settings for optimal performance:
   ```
   upload_max_filesize = 20M
   post_max_size = 20M
   memory_limit = 128M
   max_execution_time = 120
   ```

8. Access the application through your web browser
   - Navigate to: http://your-site-url/
   - Login with default credentials
   - Begin setting up your classroom environment

### Directory Permissions
Make sure these directories are writable by the web server user (www-data, apache, nginx, etc.):
  - `/uploads/avatars`: For profile pictures (755)
  - `/uploads/assignments`: For teacher assignments (755)
  - `/uploads/submissions`: For student submissions (755)
  - `/uploads/challenges`: For challenge files (755)
  - `/img`: For system images (755)

### Web Server Configuration
#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName classroom.example.com
    DocumentRoot /var/www/html/classroom
    
    <Directory /var/www/html/classroom>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/classroom_error.log
    CustomLog ${APACHE_LOG_DIR}/classroom_access.log combined
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name classroom.example.com;
    root /var/www/html/classroom;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

## Usage

### Teacher Access
- Login using teacher credentials (default: teacher1 / 123456a@A)
- Create assignments for students:
  1. Navigate to "Assignments" section in the main navigation
  2. Click "Create Assignment" button in the top right corner
  3. Fill in the assignment title (required)
  4. Add a detailed description explaining the assignment requirements
  5. Upload an assignment file (supported formats: PDF, DOC, DOCX, TXT, ZIP)
  6. Click "Create Assignment" to publish it to all students
  7. Verify the assignment appears in the assignments list
- Create challenges for students to solve:
  1. Navigate to "Challenges" section in the main navigation
  2. Click "Create Challenge" button
  3. Provide a helpful hint that guides students without giving away the answer
  4. Enter the expected result (the answer students must guess)
  5. Upload a challenge file containing the problem statement or data (TXT format)
  6. Click "Create Challenge" to make it available to students
  7. Students will only see the challenge content after providing the correct answer
- View student submissions:
  1. Navigate to "Submissions" in the main navigation
  2. Browse the complete list of all student submissions
  3. Filter submissions by assignment using the dropdown menu
  4. Download submitted files by clicking the "Download" button
  5. View the student's profile by clicking "Student Profile"
  6. Track submission dates and monitor student progress
- Communicate with students:
  1. Navigate to "User Directory" on the homepage
  2. Find the student you want to contact
  3. Click "View Profile" to access their profile page
  4. Scroll down to the messaging section
  5. Type your message in the text area
  6. Click "Send Message" to deliver it to the student
  7. Messages are private between you and the student
  8. Edit or delete messages using the options next to each sent message

### Student Access
- Login using student credentials (default: student1 / 123456a@A)
- View and download assignments:
  1. Navigate to "Assignments" section in the main navigation
  2. Browse the list of all available assignments
  3. View assignment details including title, description, and teacher name
  4. Download assignment files by clicking the "Download" button
  5. Note the submission status (submitted/not submitted) for each assignment
- Submit completed assignments:
  1. Navigate to "Assignments" section
  2. Find the assignment you want to submit
  3. Click "Submit" button on the right side of the assignment row
  4. If updating a previous submission, you'll see your current submission
  5. Click "Choose file" to select your completed work
  6. Select the appropriate file (supported formats: PDF, DOC, DOCX, TXT, ZIP)
  7. Click "Submit Assignment" to upload your work
  8. Receive confirmation that your submission was successful
- Attempt to solve challenges:
  1. Navigate to "Challenges" section in the main navigation 
  2. Browse available challenges created by teachers
  3. Click on a challenge to expand its details
  4. Read the hint provided by the teacher
  5. Enter your answer in the input field
  6. Click "Submit Answer" to check if you're correct
  7. If correct, the challenge content will be revealed
  8. You can also download the challenge file for offline viewing
- Communicate with teachers and other students:
  1. Navigate to "User Directory" on the homepage
  2. Find the user you want to contact (teacher or student)
  3. Click "View Profile" to access their profile page
  4. Scroll down to the messaging section
  5. Type your message in the text area
  6. Click "Send Message" to deliver it
  7. View conversation history in chronological order
  8. Edit or delete your own messages as needed
- Track your submissions:
  1. Navigate to "My Submissions" section
  2. View a list of all assignments you've submitted
  3. See submission dates and file information
  4. Download your submissions for reference
  5. Update submissions by clicking "Update" if needed

### Additional Features

#### Profile Management
1. Click on your name in the top-right corner
2. View your profile information
3. Click "Edit Profile" to modify your details
4. Update your email and phone number
5. Upload a new avatar image or provide an avatar URL
6. Click "Save Changes" to update your profile

#### Message Management
1. Access your profile page to view recent messages
2. Click on a sender's "Reply" button to respond quickly
3. Edit messages by clicking the "Edit" link
4. Delete messages using the "Delete" option
5. Messages are marked as read automatically when viewed

#### Dashboard Access
1. Navigate to the homepage after logging in
2. View the user directory with all system users
3. Access role-specific dashboard elements
4. Teachers see management options
5. Students see submission and assignment shortcuts

## Project Pages

### Public Pages
- `login.php`: User authentication
  - Login form with username and password fields
  - Error handling for invalid credentials
  - Redirection to appropriate dashboard based on role
  - Default credentials display for demonstration purposes
- `logout.php`: Session termination
  - Secure session destruction
  - Redirection to login page
  - Prevention of session fixation attacks

### Student & Teacher Pages
- `index.php`: User directory and dashboard
  - Complete user listing with profile information
  - Role-based dashboard sections
  - Quick access to frequently used functions
  - System announcements and updates
- `assignments.php`: Assignment listing
  - Complete assignment list
  - Download functionality for assignment files
  - Submission status indicators for students
  - Assignment creation button for teachers
- `challenges.php`: Challenge listing
  - Available challenges with hints
  - Answer submission form for students
  - Content reveal upon correct answers
  - Challenge creation button for teachers
- `profile.php`: User profile viewing and messaging
  - Personal information display
  - Contact details
  - Direct messaging functionality
  - Profile editing capabilities

### Teacher-Only Pages
- `create-assignment.php`: Assignment creation
  - Assignment details form
  - File upload functionality
  - Validation and error handling
  - Success confirmation and redirection
- `create-challenge.php`: Challenge creation
  - Challenge details form
  - Expected answer setting
  - File upload for challenge content
  - Validation and security checks
- `submissions.php`: View all student submissions
  - Comprehensive submission listing
  - Filtering by assignment
  - Download functionality
  - Student profile links
- `manage-students.php`: Student management
  - Student directory
  - Profile access
  - Contact information
  - Profile picture display

### Student-Only Pages
- `submit-assignment.php`: Assignment submission
  - File upload interface
  - Validation and error handling
  - Previous submission display
  - Confirmation messages
- `my-submissions.php`: View personal submission history
  - Complete submission history
  - Download functionality
  - Update submission capability
  - Timestamp information

### Utility Pages
- `serve-file.php`: Secure file download handler
  - Authenticated file access
  - MIME type detection
  - Proper HTTP headers
  - Access control based on file type and user role

## Security Features

- Password hashing using PHP's native password functions
  - BCrypt algorithm with cost factor 10
  - Automatic salt generation and management
  - Future-proof design for algorithm upgrades
  - Protection against rainbow table attacks
- Input sanitization for all user-provided data
  - HTML special character encoding
  - MySQL real escape string for database inputs
  - Whitelist validation for critical inputs
  - Multiple sanitization layers
- Prevention of directory traversal attacks through path validation
  - Realpath function to resolve canonical paths
  - Path component validation
  - Restriction to whitelisted directories
  - Error logging for suspicious path requests
- MIME type validation for uploaded and downloaded files
  - Extension checking
  - Content-based type detection
  - Whitelist of allowed types per context
  - Default safe content types
- Session management with proper security controls
  - Session timeout configuration
  - Secure cookie settings (httponly, secure, samesite attributes)
  - Session regeneration on privilege changes
  - Session fixation prevention
- Role-based access control for all operations
  - Permission verification on every protected page
  - UI element visibility control based on role
  - Function-level permission checking
  - Graceful handling of unauthorized attempts
- XSS prevention through output escaping
  - HTML entity encoding for displayed data
  - Context-aware escaping
  - JavaScript string sanitization
  - Parameter encoding in URLs

## Troubleshooting

### Common Issues and Solutions

#### Upload Permissions
- **Issue**: Files cannot be uploaded, "Failed to create directory" errors
- **Solution**: 
  ```bash
  chmod -R 755 /path/to/uploads
  chown -R www-data:www-data /path/to/uploads
  ```
- **Verification**: Check directory permissions with `ls -la /path/to/uploads`
- **Prevention**: Always verify directory permissions after installation

#### Database Connection
- **Issue**: "Connection failed" errors, blank pages
- **Solution**: Verify credentials in config.php and database existence
  ```php
  define('DB_HOST', 'correct_host');
  define('DB_USER', 'correct_username');
  define('DB_PASS', 'correct_password');
  ```
- **Verification**: Test connection manually with: 
  ```php
  $test = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  echo mysqli_connect_error();
  ```
- **Prevention**: Use environment variables or secure credential storage

#### File Uploads Fail
- **Issue**: Large files fail to upload, no error messages
- **Solution**: Check and increase PHP limits in php.ini

### Security Best Practices

#### Regular Security Audits
- Conduct periodic code reviews focusing on security
- Use security scanning tools to identify vulnerabilities
- Keep all dependencies updated to latest secure versions
- Monitor application logs for suspicious activities

#### Additional Security Layers
- Consider implementing rate limiting to prevent brute force attacks
- Add CAPTCHA for sensitive operations to prevent automated attacks
- Implement IP-based restrictions for administrative functions
- Consider two-factor authentication for sensitive roles

#### Data Protection
- Ensure sensitive data is encrypted at rest
- Use TLS/SSL for all communications
- Implement proper backup procedures with encryption
- Have a data breach response plan

#### Security Headers
- Implement HTTP security headers:
  ```
  Content-Security-Policy: default-src 'self'
  X-Content-Type-Options: nosniff
  X-Frame-Options: DENY
  X-XSS-Protection: 1; mode=block
  Strict-Transport-Security: max-age=31536000; includeSubDomains
  ```
- Configure web server to remove version information
- Use HTTPS-only cookies

## Development and Extension

The system is designed to be easily extended:

### Adding New Models
- Create a new PHP class in the `/models` directory
- Extend the base Model class to inherit CRUD functionality
- Define the table name and any custom fields
- Add specific methods for the entity's business logic
- Register the new model in `init.php` if needed

Example:
```php
class Category extends Model {
    protected $table = 'categories';
    
    public function getCategoriesWithCounts() {
        // Custom method implementation
    }
}
```

### Implementing New Features
- Add new page scripts for user interface
- Create supporting model methods for data handling
- Implement any required utility functions
- Update navigation and access control as needed

### Extending File Handler Capabilities
- Add methods to the FileHandler class for new functionality
- Update MIME type mappings for new file formats
- Implement additional validation routines
- Add specialized processing for specific file types

### Adding New User Roles
- Modify the `users` table to add new role values
- Update authentication functions in `functions.php`
- Create role-specific views and permissions
- Implement access control for the new role
