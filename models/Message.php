<?php
require_once 'Model.php';

class Message extends Model {
    protected $table = 'messages';
    
    /**
     * Get recent unread messages for a user
     * 
     * @param int $userId User ID
     * @return array Recent unread messages with sender details
     */
    public function getUnreadMessages($userId) {
        $sql = "SELECT m.*, u.fullname, u.avatar, u.role
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE m.receiver_id = ? AND m.is_read = 0
                ORDER BY m.created_at DESC";
        
        return $this->query($sql, "i", [$userId]);
    }
    
    /**
     * Get conversation between two users
     * 
     * @param int $user1 First user ID
     * @param int $user2 Second user ID
     * @return array Messages between the users
     */
    public function getConversation($user1, $user2) {
        $sql = "SELECT m.*, u.fullname, u.avatar 
                FROM messages m 
                JOIN users u ON m.sender_id = u.id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC";
        
        return $this->query($sql, "iiii", [$user1, $user2, $user2, $user1]);
    }
    
    /**
     * Mark messages as read
     * 
     * @param int $receiverId Receiver user ID
     * @param int $senderId Sender user ID
     * @return bool True on success, false on failure
     */
    public function markAsRead($receiverId, $senderId) {
        $sql = "UPDATE messages SET is_read = 1 
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        
        return $this->execute($sql, "ii", [$senderId, $receiverId]) !== false;
    }
}
?>
