<?php
class Database {
    private $host = "localhost";
    private $db_name = "isp_manager";
    private $username = "root"; // Change to your DB username
    private $password = "";     // Change to your DB password
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>

<!-- MySQL 
database name u951246149_isp_manager
MySQL username u951246149_amarit
Password (S47i61t56o08L)
 -->



<!-- return [
    'app_name' => 'Gym Tracker',
    'db_host' => 'localhost',
    'db_name' => 'u951246149_gym_tracker',
    'db_user' => 'u951246149_gym',
    'db_pass' => 'S47i61t56o08L',
    'db_charset' => 'utf8mb4',
]; -->