<?php
require_once 'Model.php';

class Assignment extends Model {
    protected $table = 'assignments';
    
    /**
     * Get assignments with teacher details
     * 
     * @param string|null $teacherId Optional filter by teacher ID
     * @return array List of assignments with teacher details
     */
    public function getAssignmentsWithTeacher($teacherId = null) {
        $sql = "SELECT assignments.*, users.fullname AS teacher_name 
                FROM assignments 
                JOIN users ON assignments.teacher_id = users.id ";
        
        $params = [];
        $types = "";
        
        if ($teacherId !== null) {
            $sql .= "WHERE assignments.teacher_id = ? ";
            $params[] = $teacherId;
            $types = "s"; // Changed from "i" to "s" for UUID
        }
        
        $sql .= "ORDER BY assignments.created_at DESC";
        
        return $this->query($sql, $types, $params);
    }
    
    /**
     * Get assignments for a student with submission status
     * 
     * @param string $studentId The student ID
     * @return array Assignments with submission status
     */
    public function getAssignmentsForStudent($studentId) {
        $assignments = $this->getAssignmentsWithTeacher();
        
        foreach ($assignments as &$assignment) {
            $sql = "SELECT COUNT(*) as count FROM submissions 
                    WHERE assignment_id = ? AND student_id = ?";
            $result = $this->queryOne($sql, "ss", [$assignment['id'], $studentId]); // Changed from "ii" to "ss" for UUID
            $assignment['has_submitted'] = ($result && $result['count'] > 0);
        }
        
        return $assignments;
    }
    
    /**
     * Get assignments for a student with submission status, filtered by teacher
     * 
     * @param string $studentId The student ID
     * @param string $teacherId The teacher ID
     * @return array Assignments with submission status
     */
    public function getAssignmentsForStudentByTeacher($studentId, $teacherId) {
        $assignments = $this->getAssignmentsWithTeacher($teacherId);
        
        foreach ($assignments as &$assignment) {
            $sql = "SELECT COUNT(*) as count FROM submissions 
                    WHERE assignment_id = ? AND student_id = ?";
            $result = $this->queryOne($sql, "ss", [$assignment['id'], $studentId]); // Changed from "ii" to "ss" for UUID
            $assignment['has_submitted'] = ($result && $result['count'] > 0);
        }
        
        return $assignments;
    }
}
?>
