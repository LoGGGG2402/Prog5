<?php
require_once 'Model.php';

class Message extends Model {
    protected $table = 'messages';
    
    
    /**
     * Get unread messages for a specific user
     * @param int $user_id The user ID to get messages for
     * @return array Array of unread messages with sender information
     */
    public function getUnreadMessages($user_id) {
        $sql = "SELECT m.*, u.fullname, u.avatar, u.role 
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.receiver_id = ? AND m.is_read = 0
                ORDER BY m.created_at DESC";
        return $this->query($sql, "i", [$user_id]);
    }
    
    /**
     * Get conversation between two users
     * @param int $user1_id First user ID
     * @param int $user2_id Second user ID
     * @return array Array of messages between the two users
     */
    public function getConversation($user1_id, $user2_id) {
        $sql = "SELECT m.*, u.fullname, u.avatar, u.role 
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC";
        return $this->query($sql, "iiii", [$user1_id, $user2_id, $user2_id, $user1_id]);
    }
    
    /**
     * Get latest messages for a user
     * 
     * @param string $userId User ID
     * @param int $limit Maximum number of messages to return
     * @return array Latest messages
     */
    public function getLatestMessagesForUser($userId, $limit = 10) {
        $sql = "SELECT messages.*, users.fullname
                FROM messages
                JOIN users ON messages.sender_id = users.id
                WHERE receiver_id = ?
                ORDER BY created_at DESC
                LIMIT ?";
        
        return $this->query($sql, "si", [$userId, $limit]);
    }
    
    /**
     * Mark messages as read when receiver views messages from a specific sender
     * 
     * @param int $receiverId Receiver (current user) ID
     * @param int $senderId Sender user ID
     * @return bool Success status
     */
    public function markAsRead($receiverId, $senderId) {
        $sql = "UPDATE {$this->table} SET is_read = 1 
                WHERE receiver_id = ? AND sender_id = ? AND is_read = 0";
        
        return $this->execute($sql, "ii", [$receiverId, $senderId]) !== false;
    }
    
    /**
     * Mark a single message as read by ID
     * 
     * @param int $messageId Message ID to mark as read
     * @return bool Success status
     */
    public function markSingleAsRead($messageId) {
        return $this->update($messageId, ['is_read' => 1]);
    }
    
    /**
     * Create a new message with UUID
     * 
     * @param array $data Message data
     * @return string|bool UUID of created message or false on failure
     */
    public function create($data) {
        if (!isset($data['id'])) {
            $data['id'] = generate_uuid();
        }
        
        return parent::create($data);
    }
}
?>
