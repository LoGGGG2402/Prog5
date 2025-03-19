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
     * Get all students
     * 
     * @return array List of students
     */
    public function getAllStudents() {
        return $this->findBy('role', 'student');
    }
    
    /**
     * Create a new user with password hashing
     * 
     * @param array $data User data including plain text password
     * @return int|false New user ID or false on failure
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Update a user, handling password hashing if needed
     * 
     * @param int $id User ID
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
     * Check if user has unread messages
     * 
     * @param int $userId User ID
     * @return bool True if has unread messages, false otherwise
     */
    public function hasUnreadMessages($userId) {
        $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
        $result = $this->queryOne($sql, "i", [$userId]);
        return $result && $result['count'] > 0;
    }
}
?>
