<?php
require_once 'Model.php';

class User extends Model {
    protected $table = 'users';
    
    /**
     * Find a user by username
     * 
     * @param string $username The username to find
     * @return array|null User data or null if not found
     */
    public function findByUsername($username) {
        return $this->findOneBy('username', $username);
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username The username
     * @param string $password The plain text password
     * @return array|false User data or false if authentication failed
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get all teachers
     * 
     * @return array List of teachers
     */
    public function getAllTeachers() {
        return $this->findBy('role', 'teacher');
    }
    
    /**
     * Get teacher name by ID
     * 
     * @param string $id Teacher ID
     * @return string Teacher name or 'Unknown Teacher' if not found
     */
    public function getTeacherName($id) {
        $teacher = $this->find($id);
        return $teacher ? $teacher['fullname'] : 'Unknown Teacher';
    }
    
    /**
     * Get all students
     * 
     * @return array List of students
     */
    public function getAllStudents() {
        return $this->findBy('role', 'student');
    }
    
    /**
     * Update a user, handling password hashing if needed
     * 
     * @param string $id User ID
     * @param array $data User data, possibly including new password
     * @return bool True on success, false on failure
     */
    public function updateUser($id, $data) {
        // If password is provided but empty, remove it from update data
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        // If password is provided and not empty, hash it
        elseif (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Create a new user with proper password hashing
     * 
     * @param array $data User data including password
     * @return string|bool ID of created user or false on failure
     */
    public function create($data) {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Generate UUID if not provided
        if (!isset($data['id'])) {
            $data['id'] = generate_uuid();
        }
        
        return parent::create($data);
    }
}
?>
