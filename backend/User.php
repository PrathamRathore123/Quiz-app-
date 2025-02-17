<?php
require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $name = $this->db->escape($data['name']);
        $email = $this->db->escape($data['email']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $points = 100; // Initial points for new users

        $query = "INSERT INTO users (name, email, password, points) 
                  VALUES ('$name', '$email', '$password', $points)";
        
        if ($this->db->query($query)) {
            return [
                'success' => true,
                'user' => [
                    'id' => $this->db->lastInsertId(),
                    'name' => $name,
                    'email' => $email,
                    'points' => $points
                ]
            ];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function login($email, $password) {
        $startTime = microtime(true);
        $email = $this->db->escape($email);
        error_log("Starting login process for email: $email");

        // Fetch only the necessary columns to reduce data transfer
        $query = "SELECT id, name, email, password, points FROM users WHERE email = '$email'";
        $queryStart = microtime(true);
        $result = $this->db->query($query);
        $queryEnd = microtime(true);
        error_log("Query execution time: " . ($queryEnd - $queryStart) . " seconds");

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $verifyStart = microtime(true);
            $passwordValid = password_verify($password, $user['password']);
            $verifyEnd = microtime(true);
            error_log("Password verification time: " . ($verifyEnd - $verifyStart) . " seconds");

            if ($passwordValid) {
                unset($user['password']);
                $totalTime = microtime(true) - $startTime;
                error_log("Login successful. Total time: $totalTime seconds");
                return ['success' => true, 'user' => $user];
            }
        }

        $totalTime = microtime(true) - $startTime;
        error_log("Login failed. Total time: $totalTime seconds");
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    public function updatePoints($userId, $points) {
        error_log("Starting points update for user $userId with points $points");
        
        $userId = $this->db->escape($userId);
        $points = (int)$points;
        
        // Get current points for logging
        $currentQuery = "SELECT points FROM users WHERE id = $userId";
        $currentResult = $this->db->query($currentQuery);
        
        if ($currentResult->num_rows > 0) {
            $currentPoints = $currentResult->fetch_assoc()['points'];
            error_log("Current points for user $userId: $currentPoints");
        } else {
            error_log("User $userId not found when checking current points");
            return false;
        }
        
        // Update points by adding to existing points
        $query = "UPDATE users SET points = points + $points WHERE id = $userId";
        error_log("Executing query: $query");
        
        $result = $this->db->query($query);
        
        if ($result) {
            error_log("Successfully updated points for user $userId");
            return true;
        } else {
            error_log("Failed to update points for user $userId. Error: " . $this->db->getError());
            return false;
        }
    }

    public function getUserById($userId) {
        $userId = $this->db->escape($userId);
        $query = "SELECT id, name, email, points FROM users WHERE id = '$userId'";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
