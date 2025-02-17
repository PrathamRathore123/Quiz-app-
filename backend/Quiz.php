<?php
class Quiz {
    private $db;

    public function __construct() {
        require_once 'Database.php';
        $this->db = new Database();
    }

    // Method to save quiz results
    public function saveResult($userId, $category, $correct, $total, $time) {
        $userId = $this->db->escape($userId);
        $category = $this->db->escape($category);
        $correct = $this->db->escape($correct);
        $total = $this->db->escape($total);
        $time = $this->db->escape($time);

        $query = "INSERT INTO quiz_results (user_id, category, correct_answers, total_questions, time_taken) 
                  VALUES ('$userId', '$category', '$correct', '$total', '$time')";
        return $this->db->query($query);
    }

    // Method to get results for a user
    public function getResults($userId) {
        $userId = $this->db->escape($userId);
        $query = "SELECT * FROM quiz_results WHERE user_id = '$userId' ORDER BY created_at DESC";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
