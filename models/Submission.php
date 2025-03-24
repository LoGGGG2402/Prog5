<?php
require_once 'Model.php';

class Submission extends Model {
    protected $table = 'submissions';
    
    /**
     * Get submissions with details
     * 
     * @param array $filters Optional filters like assignment_id, student_id
     * @return array Submissions with details
     */
    public function getSubmissionsWithDetails($filters = []) {
        $sql = "SELECT submissions.*, 
                users.fullname AS student_name, users.username, users.avatar,
                assignments.title AS assignment_title 
                FROM submissions 
                JOIN users ON submissions.student_id = users.id 
                JOIN assignments ON submissions.assignment_id = assignments.id 
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['assignment_id'])) {
            $sql .= " AND submissions.assignment_id = ?";
            $params[] = $filters['assignment_id'];
            $types .= "i";
        }
        
        if (!empty($filters['student_id'])) {
            $sql .= " AND submissions.student_id = ?";
            $params[] = $filters['student_id'];
            $types .= "i";
        }
        
        $sql .= " ORDER BY submissions.created_at DESC";
        
        return $this->query($sql, $types, $params);
    }
    
    /**
     * Find a submission by assignment and student
     * 
     * @param int $assignmentId Assignment ID
     * @param int $studentId Student ID
     * @return array|null Submission or null if not found
     */
    public function findByAssignmentAndStudent($assignmentId, $studentId) {
        $sql = "SELECT * FROM submissions 
                WHERE assignment_id = ? AND student_id = ?";
        
        return $this->queryOne($sql, "ii", [$assignmentId, $studentId]);
    }
    
    /**
     * Create or update a submission
     * 
     * @param array $data Submission data
     * @return array Result with success/message/id
     */
    public function saveSubmission($data) {
        $existing = $this->findByAssignmentAndStudent(
            $data['assignment_id'], 
            $data['student_id']
        );
        
        if ($existing) {
            // Update existing submission
            $updateData = [
                'file_path' => $data['file_path'],
                'filename' => $data['filename']
            ];
            
            $success = $this->update($existing['id'], $updateData);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Your submission has been updated!',
                    'id' => $existing['id']
                ];
            }
        } else {
            // Create new submission
            $id = $this->create($data);
            
            if ($id) {
                return [
                    'success' => true,
                    'message' => 'Your submission has been received!',
                    'id' => $id
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Database error: Unable to save submission'
        ];
    }
}
?>
