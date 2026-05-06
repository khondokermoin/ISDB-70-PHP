<?php
class Package {
    private $conn;
    private $table_name = "packages";

    public function __construct($db) {
        $this->conn = $db;
    }

    // আগের মেথড: শুধুমাত্র এক্টিভ প্যাকেজ দেখার জন্য (ইউজারদের জন্য)
    public function getAllActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active' ORDER BY price ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // নতুন: অ্যাডমিন প্যানেলের জন্য সব প্যাকেজ দেখার মেথড
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY package_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // নতুন: নির্দিষ্ট একটি প্যাকেজের ডেটা আনার মেথড (Edit করার জন্য)
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE package_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // নতুন: প্যাকেজ ডিলিট করার মেথড
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE package_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>