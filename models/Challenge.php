<?php
require_once 'Model.php';

class Challenge extends Model {
    protected $table = 'challenges';
    
    /**
     * Get challenges with teacher details
     * 
     * @param string|null $teacherId Optional filter by teacher ID
     * @return array List of challenges with teacher details
     */
    public function getChallengesWithTeacher($teacherId = null) {
        $sql = "SELECT challenges.*, users.fullname AS teacher_name 
                FROM challenges 
                JOIN users ON challenges.teacher_id = users.id ";
        
        $params = [];
        $types = "";
        
        if ($teacherId !== null) {
            $sql .= "WHERE challenges.teacher_id = ? ";
            $params[] = $teacherId;
            $types = "s"; // Use string type for UUID
        }
        
        $sql .= "ORDER BY challenges.created_at DESC";
        
        return $this->query($sql, $types, $params);
    }
    
    /**
     * Create a new challenge with UUID
     * 
     * @param array $data Challenge data
     * @return string|bool UUID of created challenge or false on failure
     */
    public function create($data) {
        if (!isset($data['id'])) {
            $data['id'] = generate_uuid();
        }
        
        return parent::create($data);
    }
    
    /**
     * Get challenge with file content
     * 
     * @param int $id Challenge ID
     * @return array|null Challenge with file content or null
     */
    public function getChallengeWithContent($id) {
        $challenge = $this->find($id);
        
        if ($challenge && file_exists($challenge['file_path'])) {
            $challenge['content'] = file_get_contents($challenge['file_path']);
        } else {
            $challenge['content'] = "File not found";
        }
        
        return $challenge;
    }
    
    /**
     * Check if a challenge result matches
     * 
     * @param int $challengeId Challenge ID
     * @param string $answer User's answer
     * @return bool True if correct, false otherwise
     */
    public function checkAnswer($challengeId, $answer) {
        $challenge = $this->find($challengeId);
        
        if ($challenge) {
            return strtolower($answer) === strtolower($challenge['result']);
        }
        
        return false;
    }
}
?>
